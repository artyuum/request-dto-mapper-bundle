<?php

namespace Tests\Source;

use Artyum\RequestDtoMapperBundle\Source\FileSource;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileSourceTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(SourceInterface::class, new FileSource());
    }

    public function testRequestDataExtraction(): void
    {
        $expectedData = [
            new UploadedFile(path: __DIR__.'/Fixture/sample.txt', originalName: 'sample', test: true)
        ];
        $request = new Request(files: $expectedData);
        $source = new FileSource();

        $extractedData = $source->extract($request);

        self::assertEquals($expectedData, $extractedData);
    }
}
