<?php

namespace Tests\Source;

use Artyum\RequestDtoMapperBundle\Source\QueryStringSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryStringSourceTest extends TestCase
{
    public function testRequestDataExtraction(): void
    {
        $expectedData = [
            'key' => 'value'
        ];
        $request = new Request(query: $expectedData);
        $source = new QueryStringSource();

        $extractedData = $source->extract($request);

        self::assertEquals($expectedData, $extractedData);
    }
}
