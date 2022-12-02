# Request DTO Mapper Bundle
![image](https://user-images.githubusercontent.com/17199757/193117824-e5eec5b6-f4c0-4c96-af9b-fa2bc6096806.png)

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
# config/packages/artyum_request_dto_mapper.yaml
artyum_request_dto_mapper:

    # Used if the attribute does not specify any (must be a FQCN implementing "\Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface").
    default_extractor: null # Example: Artyum\RequestDtoMapperBundle\Extractor\JsonExtractor

    # The configuration related to the denormalizer (https://symfony.com/doc/current/components/serializer.html).
    denormalizer:

        # Used when mapping the request data to the DTO if the attribute does not set any.
        default_options: []

        # Used when mapping the request data to the DTO (merged with the values passed by the attribute or "default_options").
        additional_options: []

    # The configuration related to the validator (https://symfony.com/doc/current/validation.html).
    validation:

        # Whether to validate the DTO after mapping it.
        enabled: false

        # Used when validating the DTO if the attribute does not set any.
        default_groups: []

        # Used when validating the DTO (merged with the values passed by the attribute or "default_groups").
        additional_groups: []

        # Whether to throw an exception if the DTO validation failed (constraint violations).
        throw_on_violation: true
```

## Usage
This is a simple step-by-step example of how to make a DTO that will be used by the bundle.

1. Create the DTO that represents the structure of the content the user will send to your controller. 
```php
class PostPayload {    
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
use Artyum\RequestDtoMapperBundle\Extractor\JsonExtractor;

class CreatePostController extends AbstractController
{
    #[Dto(extractor: JsonExtractor::class, subject: PostPayload::class, validate: true)]
    public function __invoke(PostPayload $postPayload): Response
    {
        // At this stage, your DTO has automatically been mapped (from the JSON input) and validated.
        // Your controller can safely be executed knowing that the submitted content
        // matches your requirements (defined in your DTO through the validator constraints).
    }
}
```

**Alternatively**, you can set the attribute directly on the argument:
```php
public function __invoke(#[Dto(extractor: JsonExtractor::class, validate: true)] PostPayload $postPayload): Response
{
}
```

If you have set some default options in the configuration file (the default extractor to use, whether to enable the validation), you can even make it shorter:
```php
public function __invoke(#[Dto] PostPayload $postPayload): Response
{
}
```

3. That's it!

## Attribute
The [Dto attribute](src/Attribute/Dto.php) has the following seven properties:

### 1. Extractor
The FQCN (Fully-Qualified Class Name) of a class that implements the `ExtractorInterface`. It basically contains the extraction logic and it's called by the mapper in order to extract the data from the request.

The bundle already comes with 3 built-in extractors that should meet most of your use-cases:
- [BodyParameterExtractor](/src/Extractor/BodyParameterExtractor.php) (extracts the data from `$request->request->all()`)
- [JsonExtractor](/src/Extractor/JsonExtractor.php) (extracts the data from `$request->toArray()`)
- [QueryStringExtractor](/src/Extractor/QueryStringExtractor.php) (extracts the data from `$request->query->all()`)

If an error occurs when the `extract()` method is called from the extractor class, the [ExtractionFailedException](src/Exception/ExtractionFailedException.php) will be thrown.

If these built-in extractor classes don't meet your needs, you can implement your own extractor like this:

```php
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomExtractor implements ExtractorInterface
{
    // you can optionally inject dependencies
    public function __construct() {
    }

    public function extract(Request $request): array
    {
        // your custom extraction logic here 
    }
}
```
Then pass it to the `Dto` attribute like this:

```php
#[Dto(extractor: CustomExtractor::class)]
```

If you don't set any value, the default value (defined in the bundle's configuration file) will be used.

**Note:** All classes implementing `ExtractorInterface` are automatically tagged as "artyum_request_dto_mapper.extractor",
and this is needed by the mapper in order to retrieve the needed extractor class instance from the container.

### 2. Subject
The FQCN (Fully-Qualified Class Name) of the DTO you want to map (it must be present as your controller argument).

The "subject" property is required **only** if you're setting the attribute directly on the method. Example:

```php
#[Dto(subject: PostPayload::class)]
public function __invoke(PostPayload $postPayload): Response
{
}
```

If you're setting the attribute on the method argument instead, the "subject" value can be omitted and won't be read by the mapper. Example:
```php
public function __invoke(#[Dto] PostPayload $postPayload): Response
{
}
```

### 3. Methods
It can contain a single or an array of HTTP methods that will "enable" the mapping/validation depending on the current HTTP method. In the following example, the DTO will be mapped & validated only if the request method is "GET".
```php
#[Dto(methods: 'GET')]
``` 
or
```php
#[Dto(methods: ['GET'])]
```

If the array is empty (this is the default value), the mapper will always map the DTO and validate it.

### 4. Denormalization Options
The options that will be passed to the [Denormalizer](https://symfony.com/doc/current/components/serializer.html) before mapping the DTO.

Example:
```php
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Dto(denormalizerOptions: [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true])]
```

If an error occurs when the `denormalize()` method is called from the Denormalizer, the [DtoMappingFailedException](src/Exception/DtoMappingFailedException.php) will be thrown.

### 5. Validate
Whether to validate the DTO (once the mapping is done). Internally, the [validator component](https://symfony.com/doc/current/validation.html) will be used, and if you do not have it installed a `LogicException` will be thrown.

Example:
```php
#[Dto(validate: true)]
```

If the validation failed (due to the constraint violations), the constraint violations will be available as request attribute:
```php
$request->attributes->get('_constraint_violations')
```

If you don't set any value, the configured value (defined in the bundle's configuration file) will be used.

### 6. Validation Groups
The [validation groups](https://symfony.com/doc/current/form/validation_groups.html) to pass to the validator.

Example:
```php
#[Dto(validationGroups: ['creation'])]
```

If you don't set any value, the configured value (defined in the bundle's configuration file) will be used.

### 7. Throw on violation
When the validation failed, the [DtoValidationFailedException](/src/Exception/DtoValidationFailedException.php) will be thrown, and you will be able to get a list of these violations by calling the `getViolations()` method.

Setting the value to `false` will prevent the exception from being thrown, and your controller will still be executed.

Example:
```php
#[Dto(throwOnViolation: false)]
```

If you don't set any value, the configured value (defined in the bundle's configuration file) will be used.

## Events
- [PreDtoMappingEvent](/src/Event/PreDtoMappingEvent.php) - dispatched before the mapping is made.
- [PostDtoMappingEvent](/src/Event/PostDtoMappingEvent.php) - dispatched once the mapping is made.
- [PreDtoValidationEvent](/src/Event/PreDtoValidationEvent.php) - dispatched before the validation is made (if the validation is enabled).
- [PostDtoValidationEvent](/src/Event/PostDtoValidationEvent.php) - dispatched once the validation is made (if the validation is enabled).
