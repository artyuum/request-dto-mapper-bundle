<?php

namespace Tests\Extractor;

use Artyum\RequestDtoMapperBundle\Extractor\QueryStringExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryStringExtractorTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(ExtractorInterface::class, new QueryStringExtractor());
    }

    public function testExtraction(): void
    {
        $expectedData = [
            'key' => 'value',
        ];
        $request = new Request(query: $expectedData);
        $extractor = new QueryStringExtractor();

        $extractedData = $extractor->extract($request);

        self::assertSame($expectedData, $extractedData);
    }
}
