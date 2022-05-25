<?php

namespace Tests\Source;

use Artyum\RequestDtoMapperBundle\Source\FormSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FormSourceTest extends TestCase
{
    public function testRequestDataExtraction(): void
    {
        $request = new Request(
            request: ['key' => 'value'],
            files: ['file' => new UploadedFile(path: __DIR__ . '/Fixture/sample.txt', originalName: 'sample', test: true)]
        );
        $source = new FormSource();
        $extractedData = $source->extract($request);
        $expectedData = [
            'key' => 'value',
            'file' => new UploadedFile(path: __DIR__ . '/Fixture/sample.txt', originalName: 'sample', test: true)
        ];

        self::assertEquals($expectedData, $extractedData);
    }
}
