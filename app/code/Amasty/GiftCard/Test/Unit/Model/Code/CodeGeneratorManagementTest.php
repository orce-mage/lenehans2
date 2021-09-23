<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\Code;

use Amasty\GiftCard\Model\Code\CodeGenerator;
use Amasty\GiftCard\Model\Code\CodeGeneratorFactory;
use Amasty\GiftCard\Model\Code\CodeGeneratorManagement;
use Amasty\GiftCard\Model\CodePool\Repository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see CodeGeneratorManagement
 */
class CodeGeneratorManagementTest extends \PHPUnit\Framework\TestCase
{
    const TEST_CODE_POOL_ID = 1;
    const TEST_QTY = 1;
    const TEST_TEMPLATE = 'TEST_{L}';

    /**
     * @var CodeGeneratorManagement
     */
    private $codeGeneratorManagement;

    /**
     * @var CodeGeneratorFactory|MockObject
     */
    private $codeGeneratorFactory;

    /**
     * @var Repository|MockObject
     */
    private $codePoolRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->codeGeneratorFactory = $this->createPartialMock(CodeGeneratorFactory::class, ['create']);
        $this->codePoolRepository = $this->createPartialMock(Repository::class, ['getById']);

        $this->codeGeneratorManagement = $objectManager->getObject(
            CodeGeneratorManagement::class,
            [
                'codeGeneratorFactory' => $this->codeGeneratorFactory,
                'codePoolRepository' => $this->codePoolRepository
            ]
        );
    }

    /**
     * @covers \Amasty\GiftCard\Model\Code\CodeGeneratorManagement::generateCodesForCodePool
     */
    public function testGenerateCodesForCodePool()
    {
        $codePool = $this->createPartialMock(\Amasty\GiftCard\Model\CodePool\CodePool::class, []);
        $codePool->setTemplate(self::TEST_TEMPLATE);
        $this->codePoolRepository->expects($this->once())->method('getById')->with(self::TEST_CODE_POOL_ID)
            ->willReturn($codePool);
        $this->initCodeGeneratorMock(self::TEST_CODE_POOL_ID, self::TEST_QTY);

        $this->assertTrue(
            $this->codeGeneratorManagement->generateCodesForCodePool(
                self::TEST_CODE_POOL_ID,
                self::TEST_QTY
            )
        );
    }

    /**
     * @covers \Amasty\GiftCard\Model\Code\CodeGeneratorManagement::generateCodesByTemplate
     */
    public function testGenerateCodesByTemplate()
    {
        $this->initCodeGeneratorMock(self::TEST_CODE_POOL_ID, self::TEST_QTY);

        $this->assertTrue($this->codeGeneratorManagement->generateCodesByTemplate(
            self::TEST_CODE_POOL_ID,
            self::TEST_TEMPLATE,
            self::TEST_QTY
        ));
    }

    /**
     * @param int $codePoolId
     * @param int $qty
     */
    private function initCodeGeneratorMock($codePoolId, $qty)
    {
        $codeGenerator = $this->createPartialMock(CodeGenerator::class, ['generateCodes']);
        $codeGenerator->expects($this->once())->method('generateCodes')->with(self::TEST_TEMPLATE, $qty);
        $this->codeGeneratorFactory->expects($this->once())->method('create')
            ->with([
                'codePoolId' => $codePoolId
            ])->willReturn($codeGenerator);
    }
}
