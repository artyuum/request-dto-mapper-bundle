<?php

namespace Tests\Source;

use Artyum\RequestDtoMapperBundle\Source\JsonSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class JsonSourceTest extends TestCase
{
    public function testRequestDataExtraction(): void
    {
        $expectedData = [
            'key' => 'value'
        ];
        $request = new Request(content: json_encode($expectedData));
        $source = new JsonSource();

        $extractedData = $source->extract($request);

        self::assertEquals($expectedData, $extractedData);
    }
}
