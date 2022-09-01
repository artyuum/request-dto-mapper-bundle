<?php

namespace Tests\Extractor;

use Artyum\RequestDtoMapperBundle\Extractor\FileExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileExtractorTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(ExtractorInterface::class, new FileExtractor());
    }

    public function testExtraction(): void
    {
        $expectedData = [
            new UploadedFile(path: __DIR__ . '/Fixture/sample.txt', originalName: 'sample', test: true),
        ];
        $request = new Request(files: $expectedData);
        $extractor = new FileExtractor();

        $extractedData = $extractor->extract($request);

        self::assertSame($expectedData, $extractedData);
    }
}
