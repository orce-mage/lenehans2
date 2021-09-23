<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Source;

use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Amasty\ImportCore\Import\Source\MimeValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\File\Mime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ImportCore\Import\Source\MimeValidator
 */
class MimeValidatorTest extends TestCase
{
    const FILE_PATH = '/path/test_file.csv';

    /**
     * @var MimeValidator
     */
    private $mimeValidator;

    /**
     * @var Mime|MockObject
     */
    private $mimeMock;

    /**
     * @var SourceConfigInterface|MockObject
     */
    private $sourceConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->mimeMock = $this->createMock(Mime::class);
        $this->sourceConfigMock = $this->createMock(SourceConfigInterface::class);
        $this->mimeValidator = $objectManager->getObject(
            MimeValidator::class,
            [
                'mime' => $this->mimeMock,
                'sourceConfig' => $this->sourceConfigMock
            ]
        );
    }

    /**
     * @param string $sourceType
     * @param array $sourceConfig
     * @param array $addMimeTypes
     * @param string $fileMimeType
     * @param bool $expectedResult
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(
        $sourceType,
        $sourceConfig,
        $addMimeTypes,
        $fileMimeType,
        $expectedResult
    ) {
        $this->sourceConfigMock->expects($this->once())
            ->method('get')
            ->with($sourceType)
            ->willReturn($sourceConfig);
        $this->mimeMock->expects($this->once())
            ->method('getMimeType')
            ->with(self::FILE_PATH)
            ->willReturn($fileMimeType);

        if ($addMimeTypes) {
            $readerReflection = new \ReflectionClass(MimeValidator::class);

            $addMimeTypesProp = $readerReflection->getProperty('additionalMimeTypes');
            $addMimeTypesProp->setAccessible(true);
            $addMimeTypesProp->setValue($this->mimeValidator, $addMimeTypes);
        }

        $this->assertEquals($expectedResult, $this->mimeValidator->isValid($sourceType, self::FILE_PATH));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                'csv',
                ['mimeTypes' => ['text/csv']],
                [],
                'text/csv',
                true
            ],
            [
                'csv',
                ['mimeTypes' => ['text/csv']],
                [],
                'text/xml',
                false
            ],
            [
                'csv',
                ['mimeTypes' => ['text/plain']],
                ['text/csv'],
                'text/csv',
                true
            ],
            [
                'csv',
                [],
                [],
                'text/csv',
                true
            ],
            [
                'csv',
                [],
                [],
                'text/xml',
                false
            ],
            [
                'csv',
                ['mimeTypes' => ['plain']],
                [],
                'text/csv',
                false
            ],
            [
                'csv',
                ['mimeTypes' => ['plain', 'text']],
                [],
                'text/csv',
                true
            ]
        ];
    }
}
