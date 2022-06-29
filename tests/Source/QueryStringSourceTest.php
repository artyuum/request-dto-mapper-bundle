<?php

namespace Tests\Source;

use Artyum\RequestDtoMapperBundle\Source\QueryStringSource;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryStringSourceTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(SourceInterface::class, new QueryStringSource());
    }

    public function testRequestDataExtraction(): void
    {
        $expectedData = [
            'key' => 'value',
        ];
        $request = new Request(query: $expectedData);
        $source = new QueryStringSource();

        $extractedData = $source->extract($request);

        self::assertSame($expectedData, $extractedData);
    }
}
