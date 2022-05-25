<?php

namespace Tests\Source;

use Artyum\RequestDtoMapperBundle\Source\BodyParameterSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BodyParameterSourceTest extends TestCase
{
    public function testRequestDataExtraction(): void
    {
        $expectedData = [
            'key' => 'value'
        ];
        $request = new Request(request: $expectedData);
        $source = new BodyParameterSource();

        $extractedData = $source->extract($request);

        self::assertEquals($expectedData, $extractedData);
    }
}
