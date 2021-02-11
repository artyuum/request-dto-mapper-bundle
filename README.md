# Request DTO Mapper Bundle
This bundle provides an easy way to automatically map the incoming request data to a DTO and optionally validate it.

## Installation
```
composer require artyuum/request-dto-mapper-bundle 
```

## Configuration
This is the default configuration (`config/packages/artyuum_request_dto_mapper_bundle.yaml`):
```yml
artyuum_request_dto_mapper_bundle:
    enabled: true # whether to enable/disable the argument resolver
```

## Usage
This is a simple example of how to make a DTO that will be used by the bundle:
```php
/**
 * @Dto(methods={"POST"}, source="json", validationGroups={"create"})
 * @Dto(methods={"PATCH"}, source="json", validationGroups={"edit"})
 */
class ArtistPayload implements DtoInterface {
    /**
     * @Assert\Sequentially({
     *     @Assert\NotBlank(groups={"create"}),
     *     @Assert\Type("string")
     * }, groups={"create", "edit"})
     *
     * @var string|null should contain the artist's name
     */
    public $name = null;
}
```

Your DTOs **must** implements the `DtoInterface` and define the request context using the `@Dto` annotation.

## Events
- **PreDtoMappingEvent** - disptached before the mapping is made, this allows you to alter the Serializer/Denormalizer options, or the Request object.
- **PreDtoValidationEvent** - dispatched before the validation is made, this allows you to alter the DTO object (if the validation is enabled).
- **PostDtoMappingEvent** - disptached at the very end of the process, this allows you to alter the DTO before it's passed to the controller.
****
