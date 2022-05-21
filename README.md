# Request DTO Mapper Bundle
![screenshot](https://user-images.githubusercontent.com/17199757/165998036-bb67d1af-f756-47fe-b9b4-f63b132c7c6f.png)

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
    #[Dto(subject: PostPayload::class, source: JsonSource::class, validate: true)]
    public function __invoke(PostPayload $postPayload): Response
    {
        // at this stage, your DTO (the PostPayload in this example) has automatically been mapped and validated
        // and your controller can safely be executed knowing that the submitted content
        // matches your requirements (defined in your DTO through the validator constraints).
    }
}
```
3. That's it!

## Events
- **[PreDtoMappingEvent](/src/Event/PreDtoMappingEvent.php)** - dispatched before the mapping is made, this allows you to alter the Request object for example.
- **[PostDtoMappingEvent](/src/Event/PostDtoMappingEvent.php)** - dispatched once the mapping is done, and it's the last event that is called before your controller is called (if the validation is not enabled).
- **[PreDtoValidationEvent](/src/Event/PreDtoValidationEvent.php)** - dispatched before the validation is made, this allows you to alter the DTO before it's being passed to the validator (if the validation is enabled).
- **[PostDtoValidationEvent](/src/Event/PostDtoValidationEvent.php)** - dispatched once the validation is done, and it's the last event that is called before your controller is called (if the validation is enabled).
