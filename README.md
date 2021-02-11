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

Your DTOs **must** implement the [`DtoInterface`](/src/Mapper/DtoInterface.php) and define the request context using the `@Dto` annotation.

Here is the list of options used in the `@Dto` annotation:
| name                 | type    | default | required | description                                                                                                       |
|----------------------|---------|---------|----------|-------------------------------------------------------------------------------------------------------------------|
| `methods`            | array   | -       | **yes**  | The HTTP method(s) on which to apply the options below.                                                           |
| `source`             | string  | -       | **yes**  | The source from where the data will be extracted from the request. (`json`, `query_strings` or `body_parameters`) |
| `validation`         | boolean | *true*  | no       | Whether or not to validate the DTO before passing it to the controller.                                           |
| `validationGroups`   | array   | *null*  | no       | The validation groups to be used when validating the DTO.                                                         |

## Events
- **PreDtoMappingEvent** - disptached before the mapping is made, this allows you to alter the Serializer/Denormalizer options, or the Request object.
- **PreDtoValidationEvent** - dispatched before the validation is made, this allows you to alter the DTO object (if the validation is enabled).
- **PostDtoMappingEvent** - disptached at the very end of the process, this allows you to alter the DTO before it's passed to the controller.

## Known limitations
1. Doesn't work with DTOs that have dependencies. (e.g. injecting a class in the DTO's constructor) 
