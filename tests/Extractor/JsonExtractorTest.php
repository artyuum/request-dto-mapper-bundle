<?php

namespace Tests\Extractor;

use Artyum\RequestDtoMapperBundle\Extractor\JsonExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class JsonExtractorTest extends TestCase
{
    public function testItImplementsTheRightInterface(): void
    {
        self::assertInstanceOf(ExtractorInterface::class, new JsonExtractor());
    }

    public function testItExtractsData(): void
    {
        $expectedData = [
            'key' => 'value',
        ];
        /** @var string $content */
        $content = json_encode($expectedData);
        $request = new Request(content: $content);
        $extractor = new JsonExtractor();

        $extractedData = $extractor->extract($request);

        self::assertSame($expectedData, $extractedData);
    }
}
