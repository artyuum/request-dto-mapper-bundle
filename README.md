# Request DTO Mapper Bundle
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
artyuum_request_dto_mapper:

    # The configuration related to the validator.
    validation:

        # Whether to validate the DTO after mapping it.
        enabled:              false

        # The default validation groups to use when validating the DTO.
        default_groups:       []

        # Whether to throw an exception if the DTO validation failed (constraint violations).
        throw_on_violation:   false

    # The default source (FQCN) to use if the attribute does not specify any.
    default_source:       ~

    # The configuration related to the denormalizer.
    denormalizer:

        # The default denormalizer options to pass when mapping the request data to the DTO.
        default_options:      []
```

## Usage
This is a simple step-by-step example of how to make a DTO that will be used by the bundle.

1. Create the DTO that represents the JSON content the user will send to your controller. 
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

class PostController extends AbstractController
{
    #[Route('/posts', name: 'post.create', methods: 'POST')]
    #[Dto(subject: PostPayload::class, source: JsonSource::class, validation: true)]
    public function __invoke(PostPayload $postPayload): Response
    {
        // at this stage, your DTO has automatically been mapped and validated (if enabled)
        // and your controller can safely be executed knowing that the submitted content
        // matches your requirements (defined in your DTO).
    }
}
```
3. That's it!

## Events
- **[PreDtoMappingEvent](/src/Event/PreDtoMappingEvent.php)** - dispatched before the mapping is made, this allows you to alter the Request object for example.
- **[PostDtoMappingEvent](/src/Event/PostDtoMappingEvent.php)** - dispatched once the mapping is done, and it's the last event that is called before your controller is called (if the validation is NOT enabled).
- **[PreDtoValidationEvent](/src/Event/PreDtoValidationEvent.php)** - dispatched before the validation is made, this allows you to alter the DTO before it's being passed to the validator.
- **[PostDtoMappingEvent](/src/Event/PostDtoMappingEvent.php)** - dispatched once the validation is done, and it's the last event that is called before your controller is called (if the validation is enabled).
