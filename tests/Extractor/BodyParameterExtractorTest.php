<?php

namespace Tests\Extractor;

use Artyum\RequestDtoMapperBundle\Extractor\BodyParameterExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BodyParameterExtractorTest extends TestCase
{
    public function testItImplementsTheRightInterface(): void
    {
        self::assertInstanceOf(ExtractorInterface::class, new BodyParameterExtractor());
    }

    public function testItExtractsData(): void
    {
        $expectedData = [
            'key' => 'value',
        ];
        $request = new Request(request: $expectedData);
        $extractor = new BodyParameterExtractor();

        $extractedData = $extractor->extract($request);

        self::assertSame($expectedData, $extractedData);
    }
}
