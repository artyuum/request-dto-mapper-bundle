<?php

namespace Tests\Extractor;

use Artyum\RequestDtoMapperBundle\Extractor\FormExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FormExtractorTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(ExtractorInterface::class, new FormExtractor());
    }

    public function testExtraction(): void
    {
        $request = new Request(
            request: ['key' => 'value'],
            files: ['file' => new UploadedFile(path: __DIR__ . '/Fixture/sample.txt', originalName: 'sample', test: true)]
        );
        $extractor = new FormExtractor();
        $extractedData = $extractor->extract($request);
        $expectedData = [
            'key'  => 'value',
            'file' => new UploadedFile(path: __DIR__ . '/Fixture/sample.txt', originalName: 'sample', test: true),
        ];

        self::assertEquals($expectedData, $extractedData);
    }
}
