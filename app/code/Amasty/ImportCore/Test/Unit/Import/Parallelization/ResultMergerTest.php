<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Parallelization;

use Amasty\ImportCore\Api\ImportResultInterface;
use Amasty\ImportCore\Import\ImportResult;
use Amasty\ImportCore\Import\Parallelization\ResultMerger;

/**
 * @covers \Amasty\ImportCore\Import\Parallelization\ResultMerger
 * @covers \Amasty\ImportCore\Import\ImportResult
 */
class ResultMergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for testMerge
     */
    public function mergeDataProvider()
    {
        $primaryResult = new ImportResult();
        $secondaryResult = new ImportResult();
        $expectedResult = new ImportResult();

        $primaryResult->setRecordsUpdated(10);
        $secondaryResult->setRecordsUpdated(100);
        $expectedResult->setRecordsUpdated(10 + 100);

        $primaryResult->logMessage(ImportResultInterface::MESSAGE_ERROR, 'error1');
        $secondaryResult->logMessage(ImportResultInterface::MESSAGE_ERROR, 'error2');
        $expectedResult->logMessage(ImportResultInterface::MESSAGE_ERROR, 'error1');
        $expectedResult->logMessage(ImportResultInterface::MESSAGE_ERROR, 'error2');

        return [
            [$primaryResult, $secondaryResult, $expectedResult]
        ];
    }

    /**
     * @dataProvider mergeDataProvider
     * @param ImportResultInterface $primaryResult
     * @param ImportResultInterface $secondaryResult
     * @param ImportResultInterface $expectedResult
     */
    public function testMerge(
        ImportResultInterface $primaryResult,
        ImportResultInterface $secondaryResult,
        ImportResultInterface $expectedResult
    ) {
        $merger = new ResultMerger();
        $merger->merge($primaryResult, $secondaryResult);
        $this->assertEquals($expectedResult->serialize(), $primaryResult->serialize());
    }

    public function testTermination()
    {
        $merger = new ResultMerger();

        $primaryResult = new ImportResult();
        $secondaryResult = new ImportResult();

        $merger->merge($primaryResult, $secondaryResult);
        $this->assertFalse($primaryResult->isImportTerminated());

        $secondaryResult->terminateImport();
        $merger->merge($primaryResult, $secondaryResult);
        $this->assertTrue($primaryResult->isImportTerminated());
    }
}
