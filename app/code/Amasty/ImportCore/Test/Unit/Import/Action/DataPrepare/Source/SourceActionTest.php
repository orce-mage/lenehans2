<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Action\DataPrepare\Source;

use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceReaderInterface;
use Amasty\ImportCore\Import\Action\DataPrepare\Source\SourceAction;
use Amasty\ImportCore\Import\Action\DataPrepare\Source\SourceDataProcessor;
use Amasty\ImportCore\Import\Source\SourceReaderAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Action\DataPrepare\Source\SourceAction
 */
class SourceActionTest extends \PHPUnit\Framework\TestCase
{
    const SOURCE_TYPE = 'csv';

    /**
     * @var SourceAction
     */
    private $sourceAction;

    /**
     * @var SourceReaderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceReaderAdapterMock;

    /**
     * @var SourceDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceDataProcessorMock;

    /**
     * @var ImportProcessInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importProcessMock;

    /**
     * @var SourceReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceReaderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->sourceReaderAdapterMock = $this->createMock(SourceReaderAdapter::class);
        $this->sourceDataProcessorMock = $this->createMock(SourceDataProcessor::class);

        $this->importProcessMock = $this->createMock(ImportProcessInterface::class);
        $this->sourceReaderMock = $this->createMock(SourceReaderInterface::class);

        $this->sourceAction = $objectManager->getObject(
            SourceAction::class,
            [
                'sourceReaderAdapter' => $this->sourceReaderAdapterMock,
                'sourceDataProcessor' => $this->sourceDataProcessorMock
            ]
        );
    }

    public function testInitialize()
    {
        $profileConfigMock = $this->createMock(ProfileConfigInterface::class);

        $this->importProcessMock->expects($this->once())
            ->method('getProfileConfig')
            ->willReturn($profileConfigMock);
        $profileConfigMock->expects($this->once())
            ->method('getSourceType')
            ->willReturn(self::SOURCE_TYPE);
        $this->sourceReaderAdapterMock->expects($this->once())
            ->method('getReader')
            ->with(self::SOURCE_TYPE)
            ->willReturn($this->sourceReaderMock);
        $this->sourceReaderMock->expects($this->once())
            ->method('initialize')
            ->with($this->importProcessMock);

        $reflection = new \ReflectionClass(get_class($this->sourceAction));
        $sourceReaderProperty = $reflection->getProperty('sourceReader');
        $sourceReaderProperty->setAccessible(true);

        $this->sourceAction->initialize($this->importProcessMock);

        $this->assertSame(
            $sourceReaderProperty->getValue($this->sourceAction),
            $this->sourceReaderMock
        );
    }
}
