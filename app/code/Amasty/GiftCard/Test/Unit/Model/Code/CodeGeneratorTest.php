<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\Code;

use Amasty\GiftCard\Model\Code\Code;
use Amasty\GiftCard\Model\Code\CodeGenerator;
use Amasty\GiftCard\Model\Code\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see CodeGenerator
 */
class CodeGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CodeGenerator
     */
    private $codeGenerator;

    /**
     * @var Repository|MockObject
     */
    private $codeRepository;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    private $escaper;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    protected function initCodeGenerator($codePoolId = null)
    {
        $this->codeRepository = $this->createPartialMock(
            Repository::class,
            [
                'getCodesByTemplate',
                'getCodesCountByTemplate',
                'getEmptyCodeModel',
                'save',
                'getAllCodes'
            ]
        );
        $this->filesystem = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $ioFile = $this->createPartialMock(\Magento\Framework\Filesystem\Io\File::class, []);

        $this->codeGenerator = $this->objectManager->getObject(
            CodeGenerator::class,
            [
                'repository' => $this->codeRepository,
                'filesystem' => $this->filesystem,
                'escaper' => $this->escaper,
                'ioFile' => $ioFile,
                'codePoolId' => $codePoolId
            ]
        );
    }

    public function testGenerateCodes()
    {
        $this->initCodeGenerator(1);
        $template = 'TEST_{D}{L}';
        $qty = 5;
        $repoTemplate = 'TEST___';

        $this->initEscaperMock($template);

        $code = $this->createPartialMock(Code::class, []);
        $this->codeRepository->expects($this->any())->method('getEmptyCodeModel')->willReturn($code);

        $this->codeRepository->expects($this->once())->method('getCodesByTemplate')
            ->with($repoTemplate)
            ->willReturn([]);
        $this->codeRepository->expects($this->once())->method('getCodesCountByTemplate')
            ->with($repoTemplate)
            ->willReturn(0);

        $this->codeGenerator->generateCodes($template, $qty);
    }

    public function testGenerateCodesWithoutCodePool()
    {
        $this->initCodeGenerator();
        $template = 'TEST_{L}';
        $qty = 1;
        $this->initEscaperMock($template);

        $this->expectExceptionMessage('Please specify Code Pool ID before codes generation');
        $this->codeGenerator->generateCodes($template, $qty);
    }

    /**
     * @dataProvider generateCodesWrongTemplateDataProvider
     */
    public function testGenerateCodesWithWrongTemplate($template, $qty, $exceptionMessage)
    {
        $this->initCodeGenerator(1);
        $this->initEscaperMock($template);

        $this->codeRepository->expects($this->any())->method('getCodesByTemplate')
            ->willReturn([]);
        $this->codeRepository->expects($this->any())->method('getCodesCountByTemplate')
            ->willReturn(0);
        $this->expectExceptionMessage($exceptionMessage);
        $this->codeGenerator->generateCodes($template, $qty);
    }

    public function testGenerateCodesFromCsv()
    {
        $this->initCodeGenerator(1);
        $file = [
            'name' => 'test.csv',
            'size' => 2000,
            'tmp_name' => 'tmp.csv'
        ];
        $this->codeRepository->expects($this->once())->method('getAllCodes')->willReturn([]);

        $stream = $this->createPartialMock(\Magento\Framework\Filesystem\File\Read::class, ['readCsv']);
        $stream->expects($this->at(0))->method('readCsv')->willReturn(['TEST_CODE1']);
        $stream->expects($this->at(1))->method('readCsv')->willReturn(['TEST_CODE2']);
        $stream->expects($this->at(2))->method('readCsv')->willReturn(['TEST_CODE3']);

        $reader = $this->createPartialMock(Read::class, ['isExist', 'openFile']);
        $reader->expects($this->once())->method('isExist')->with($file['tmp_name'])
            ->willReturn(true);
        $reader->expects($this->once())->method('openFile')->with($file['tmp_name'])
            ->willReturn($stream);

        $this->filesystem->expects($this->once())->method('getDirectoryRead')
            ->with(DirectoryList::SYS_TMP)
            ->willReturn($reader);

        $code = $this->createPartialMock(Code::class, []);
        $this->codeRepository->expects($this->any())->method('getEmptyCodeModel')->willReturn($code);

        $this->codeGenerator->generateCodesFromCsv($file);
    }

    /**
     * @dataProvider generateCodesFromCsvWrongFileDataProvider
     */
    public function testGenerateCodesFromCsvWrongFile($file, $exceptionMessage)
    {
        $this->initCodeGenerator(1);

        $this->expectExceptionMessage($exceptionMessage);
        $this->codeGenerator->generateCodesFromCsv($file);
    }

    /**
     * @param string $template
     *
     * @throws \ReflectionException
     */
    private function initEscaperMock($template)
    {
        $this->escaper->expects($this->once())->method('escapeHtml')->with($template)->willReturn($template);
    }

    /**
     * @return array
     */
    public function generateCodesWrongTemplateDataProvider()
    {
        return [
            [//first assertion - invalid template name
                'TEST',
                1,
                'Please add {L} or {D} placeholders into the template "TEST"'
            ],
            [//second assertion - invalid codes qty
                'TEST_{L}',
                CodeGenerator::MAX_QTY + 1,
                'At a time, you can generate no more than ' . CodeGenerator::MAX_QTY . ' codes'
            ],
            [
                'TEST_{D}',
                10,
                'Maximum number of code combinations for the current template is 8, 
            please update Quantity field accordingly'
            ]
        ];
    }

    /**
     * @return array
     */
    public function generateCodesFromCsvWrongFileDataProvider()
    {
        return [
            [
                [
                    'name' => 'test.jpg',
                    'size' => 200
                ],
                'Wrong file extension. Please use only .csv files.'
            ],
            [
                [
                    'name' => 'test.csv',
                    'size' => CodeGenerator::MAX_FILE_SIZE + 1
                ],
                'The file size is too big.'
            ]
        ];
    }
}
