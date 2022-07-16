# Request DTO Mapper Bundle
![image](https://user-images.githubusercontent.com/17199757/179373257-d4b8af44-4b14-402f-a6ff-bf0131029e1f.png)

This bundle provides an easy way to automatically map the incoming request data to a DTO and optionally validate it. It's using the powerful [Serializer](https://symfony.com/doc/current/components/serializer.html) component under the hood along with the [Validator](https://symfony.com/doc/current/components/validator.html) component (optional).

## Requirements
- PHP ^8.0
- Symfony ^5.0 or ^6.0

## Installation
```bash
composer require artyuum/request-dto-mapper-bundle
```

## Configuration
```yml
# config/packages/artyuum_request_dto_mapper.yaml
artyum_request_dto_mapper:

    # Used if the attribute does not specify any (must be a FQCN implementing "\Artyum\RequestDtoMapperBundle\Source\SourceInterface").
    default_source:       ~

    # The configuration related to the denormalizer (https://symfony.com/doc/current/components/serializer.html).
    denormalizer:

        # Used when mapping the request data to the DTO if the attribute does not set any.
        default_options:      []

        # Used when mapping the request data to the DTO (merged with the values passed by the attribute or "default_options").
        additional_options:   []

    # The configuration related to the validator (https://symfony.com/doc/current/validation.html).
    validation:

        # Whether to validate the DTO after mapping it.
        enabled:              false

        # Used when validating the DTO if the attribute does not set any.
        default_groups:       []

        # Used when validating the DTO (merged with the values passed by the attribute or "default_groups").
        additional_groups:    []

        # Whether to throw an exception if the DTO validation failed (constraint violations).
        throw_on_violation:   true
```

## Usage
This is a simple step-by-step example of how to make a DTO that will be used by the bundle.

1. Create the DTO that represents the structure of the content the user will send to your controller. 
```php
class PostDto {    
    /**
     * @Assert\Sequentially({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     *
     * @var string|null
     */
    public $content;
}
```

2. Inject the DTO into your controller & configure it using the [Dto attribute](/src/Attribute/Dto.php).
```php
use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Source\JsonSource;

class PostController extends AbstractController
{
    #[Dto(source: JsonSource::class, subject: PostDto::class, validate: true)]
    public function __invoke(PostDto $postDto): Response
    {
        // At this stage, your DTO has automatically been mapped and validated.
        // Your controller can safely be executed knowing that the submitted content
        // matches your requirements (defined in your DTO through the validator constraints).
    }
}
```

**Alternatively**, you can set the attribute directly on the argument:
```php
public function __invoke(#[Dto(source: JsonSource::class, validate: true)] PostDto $postDto): Response
{
}
```

3. That's it!

## Attribute
The [Dto attribute](src/Attribute/Dto.php) has the following seven properties:

### 1. Source
The FQCN (Fully-Qualified Class Name) of a class that implements the `SourceInterface`. It basically contains the extraction logic and it's called by the mapper in order to extract the data from the request.

The bundle already comes with 5 built-in sources that should meet most of your use-cases:
- [BodyParameterSource](/src/Source/BodyParameterSource.php) (extracts the data from `$request->request->all()`)
- [FileSource](/src/Source/FileSource.php) (extracts the data from `$request->files->all()`)
- [FormSource](/src/Source/FormSource.php) (extracts & merges the data from `$request->request->all()` and `$request->files->all()`)
- [JsonSource](/src/Source/JsonSource.php) (extracts the data from `$request->toArray()`)
- [QueryStringSource](/src/Source/QueryStringSource.php) (extracts the data from `$request->query->all()`)

If an error occurs after while the `extract()` method from the source class, the `SourceExtractionException` will be thrown

If these built-in source classes don't meet your needs, you can implement your own source like this:
```php
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomSource implements SourceInterface
{
    // you can optionally inject dependencies
    public function __construct() {
    }

    public function extract(Request $request): array
    {
        // custom extraction logic here 
    }
}
```
Then pass it to the `Dto` attribute like this:

```php
#[Dto(source: CustomSource::class)]
```

If you don't set any value, the default value (defined in the bundle's configuration file) will be used.

**Note:** All classes implementing the `SourceInterface` are automatically tagged as "artyum_request_dto_mapper.source",
and this is needed by the mapper in order to retrieve the needed source class instance from the container.

### 2. Subject
The FQCN (Fully-Qualified Class Name) of the DTO you want to map (it must be present as your controller argument).

The "subject" property is required **only** if you're setting the attribute directly on the method. Example:

```php
#[Dto(subject: PostDto::class)]
public function __invoke(PostDto $postDto): Response
{
}
```

If you're setting the attribute on the method argument instead, the "subject" value can be omitted and won't be read by the mapper. Example:
```php
public function __invoke(#[Dto] PostDto $postDto): Response
{
}
``` 

This is a shorter way of marking an argument that will be handled by this bundle, but if you have to set many options on the attribute, it's recommended to set the attribute on the method instead.

### 3. Methods
It can contain an array of HTTP methods that will "enable" the mapping/validation depending on the current HTTP method. In the following example, the PostDto will be mapped & validated only if the request method is "GET".
```php
#[Dto(methods: ['GET'])]
public function __invoke(PostDto $postDto): Response
{
}
``` 

If the array is empty (this is the default value), the mapper will always map the DTO and validate it.
```php
#[Dto(methods: [])]
public function __invoke(PostDto $postDto): Response
{
}
```

### 4. Denormalization Options
The options that will be passed to the [denormalizer](https://symfony.com/doc/current/components/serializer.html) before mapping the DTO.

Example:
```php
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Dto(denormalizerOptions: [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true])]
public function __invoke(PostDto $postDto): Response
{
}
```

If an error occurs while calling the `denormalize()` method from the Denormalizer, the `DtoMappingException` will be thrown.

### 5. Validate
Whether to validate the DTO (once the mapping is done). Internally, the [validator component](https://symfony.com/doc/current/validation.html) will be used, and if you do not have it installed a `LogicException` will be thrown.

```php
#[Dto(validate: true)]
public function __invoke(PostDto $postDto): Response
{
}
```
If you don't set any value, the configured value (defined in the bundle's configuration file) will be used.

### 6. Validation Groups
The [validation groups](https://symfony.com/doc/current/form/validation_groups.html) to pass to the validator.

If you don't set any value, the configured value (defined in the bundle's configuration file) will be used.

### 7. Throw on violation
If the validation failed (due to the constraint violations), the [DtoValidationException](/src/Exception/DtoValidationException.php) will be thrown, and you will be able to get a list of these violations by calling the `getViolations()` method.

Additionally, the constraint violations will be available as request attribute:
```php
$request->attributes->get('_constraint_violations')
```

Setting the value to `false` will prevent the exception from being thrown, and your controller will still be executed.

If you don't set any value, the configured value (defined in the bundle's configuration file) will be used.

## Events
- [PreDtoMappingEvent](/src/Event/PreDtoMappingEvent.php) - dispatched before the mapping is made.
- [PostDtoMappingEvent](/src/Event/PostDtoMappingEvent.php) - dispatched once the mapping is made.
- [PreDtoValidationEvent](/src/Event/PreDtoValidationEvent.php) - dispatched before the validation is made (if the validation is enabled).
- [PostDtoValidationEvent](/src/Event/PostDtoValidationEvent.php) - dispatched once the validation is made (if the validation is enabled).
