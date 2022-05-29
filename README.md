# Request DTO Mapper Bundle
<img width="920" alt="preview" src="https://user-images.githubusercontent.com/17199757/170860717-509c0d12-0810-4d8b-af84-1991c099f57b.png">

This bundle provides an easy way to automatically map the incoming request data to a DTO and optionally validate it.

## Requirements
- PHP ^8.0
- Symfony ^5.0 or ^6.0

## Installation
```
composer require artyuum/request-dto-mapper-bundle 
```

## Configuration
```yml
# config/packages/artyuum_request_dto_mapper_bundle.yaml
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
    public $title;
    
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

2. Inject the DTO into your controller & configure it using the Dto PHP attribute.
```php
use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Source\JsonSource;

class PostController extends AbstractController
{
    #[Dto(subject: PostDto::class, source: JsonSource::class, validate: true)]
    public function __invoke(PostDto $postDto): Response
    {
        // At this stage, your DTO has automatically been mapped and validated.
        // Your controller can safely be executed knowing that the submitted content
        // matches your requirements (defined in your DTO through the validator constraints).
    }
}
```
3. That's it!

## Source
The "source" is the class that implements the `SourceInterface` and it's called by the mapper when getting the data from the request.

The bundle already comes with 5 built-in sources that should meet most of your use-cases:
- [BodyParameterSource](/src/Source/BodyParameterSource.php) (extracts the data from `$request->request->all()`)
- [FileSource](/src/Source/FileSource.php) (extracts the data from `$request->files->all()`)
- [FormSource](/src/Source/FormSource.php) (extracts & merges the data from `$request->request->all()` and `$request->files->all()`)
- [JsonSource](/src/Source/JsonSource.php) (extracts the data from `$request->toArray()`)
- [QueryStringSource](/src/Source/QueryStringSource.php) (extracts the data from `$request->query->all()`)

If these built-in sources don't meet your needs, you can implement your own source like this:
```php
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomSource implements SourceInterface
{
    public function __construct() {
        // you can optionally inject dependencies
    }

    public function extract(Request $request): array
    {
        // custom extraction logic here 
    }
}
```
Then pass it to the `Dto` attribute like this:

```php
...
#[Dto(subject: PostDto::class, source: CustomSource::class)]
...
```
**Note:** All classes implementing the `SourceInterface` are automatically tagged under "artyum_request_dto_mapper.source". 
This is needed by the mapper in order to retrieve the needed source class instance from the container.

If you disabled "autoconfigure" option, you will need to explicitly tag your custom source in your application. 

## Events
- [PreDtoMappingEvent](/src/Event/PreDtoMappingEvent.php) - dispatched before the mapping is made.
- [PostDtoMappingEvent](/src/Event/PostDtoMappingEvent.php) - dispatched once the mapping is made.
- [PreDtoValidationEvent](/src/Event/PreDtoValidationEvent.php) - dispatched before the validation is made (if the validation is enabled).
- [PostDtoValidationEvent](/src/Event/PostDtoValidationEvent.php) - dispatched once the validation is made (if the validation is enabled).
