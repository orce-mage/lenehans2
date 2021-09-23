<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Test\Integration;

use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterfaceFactory;
use Amasty\ExportCore\Export\Config\Profile\Field;
use Amasty\ExportCore\Export\Config\Profile\FieldFilter;
use Amasty\ExportCore\Export\Config\ProfileConfig;
use Amasty\ExportCore\Export\FileDestination\Type\ServerFile\ConfigInterface as DestinationConfig;
use Amasty\ExportCore\Export\Filter\Type\Date\ConfigInterface as DateFilterConfig;
use Amasty\ExportCore\Export\Run;
use Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface as CsvConfig;
use Amasty\ExportCore\Test\Integration\TestModule\Model\ResourceModel\TestEntity1;
use Amasty\ExportCore\Test\Integration\TestModule\Setup\TestEntity1\CreateTable as CreateTableCommand;
use Amasty\ExportCore\Test\Integration\TestModule\Setup\TestEntity1\DropTable as DropTableCommand;
use Amasty\ExportCore\Test\Integration\Utils\ConfigManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class FullFlowTest extends \PHPUnit\Framework\TestCase
{
    use ConfigManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cleanupAll();
        $this->objectManager->get(CreateTableCommand::class)->execute();
        $this->overrideExportConfig(__DIR__ . '/_files/config/am_export_test_entity_1.xml');
    }

    protected function tearDown(): void
    {
        $this->revertExportConfigOverride();
        $this->cleanupAll();
    }

    protected function cleanupAll()
    {
        $this->objectManager->get(DropTableCommand::class)->execute();
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete('test_export.csv');
    }

    protected function setTableContents(string $tableName, array $data): int
    {
        /** @var ResourceConnection $connection */
        $connection = $this->objectManager->get(ResourceConnection::class);
        $realTableName = $connection->getTableName($tableName);

        return $connection->getConnection()->truncateTable($realTableName)
            ->insertMultiple($realTableName, $data);
    }

    public function exportRunDataProvider(): array
    {
        return [
            [
                [
                    [
                        'text_field' => 'text1',
                        'date_field' => '2020-04-01',
                        'select_field' => 'option1'
                    ],
                    [
                        'text_field' => 'text2',
                        'date_field' => '2020-01-02',
                        'select_field' => 'option1'
                    ],
                    [
                        'text_field' => 'text3',
                        'date_field' => '2020-01-03',
                        'select_field' => 'option2'
                    ],
                ],
                <<<RESULT
text_field,select_field
text2,0
text3,0

RESULT
            ],
        ];
    }

    /**
     * @magentoDbIsolation disabled
     * @dataProvider exportRunDataProvider
     * @param array $tableData
     * @param string $expectedResult
     */
    public function testExportRun(
        array $tableData,
        string $expectedResult
    ) {
        $this->setTableContents(TestEntity1::TABLE_NAME, $tableData);
        $config = [
            ProfileConfig::ENTITY_CODE            => 'test',
            ProfileConfig::STRATEGY               => 'export',
            ProfileConfig::BATCH_SIZE             => 1,
            ProfileConfig::TEMPLATE_TYPE          => 'csv',
            ProfileConfig::FILE_DESTINATION_TYPES => ['server_file'],
            ProfileConfig::FIELDS_CONFIG          => $this->getFieldsConfig(),
            ProfileConfig::FILENAME               => 'test_export'
        ];
        /** @var ProfileConfig $profileConfig */
        $profileConfig = $this->objectManager->create(
            ProfileConfig::class,
            ['data' => $config]
        );
        $csvConfig = $this->objectManager->create(CsvConfig::class);
        $profileConfig->getExtensionAttributes()->setCsvTemplate($csvConfig);

        /** @var DestinationConfig $destinationConfig */
        $destinationConfig = $this->objectManager->create(DestinationConfig::class);
        $destinationConfig->setFilename('test_export');
        $profileConfig->getExtensionAttributes()->setServerFileDestination($destinationConfig);

        /** @var Run $runner */
        $runner = $this->objectManager->create(Run::class);
        $result = $runner->execute($profileConfig, uniqid('test_'));

        $this->assertFalse(
            $result->isFailed(),
            'Export has failed with the following errors: ' . json_encode($result->getMessages())
        );

        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $varDirectory */
        $varDirectory = $this->objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::VAR_DIR);

        $this->assertTrue($varDirectory->isExist('test_export.csv'), 'Export file does not exist');
        $fileContents = $varDirectory->readFile('test_export.csv');
        $this->assertEquals($expectedResult, $fileContents);
    }

    protected function getFieldsConfig(): \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
    {
        /** @var \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface $fieldsConfig */
        $fieldsConfig = $this->objectManager->create(FieldsConfigInterfaceFactory::class)->create();

        /** @var Field $textField */
        $textField = $this->objectManager->create(Field::class);
        $textField->setName('text_field');

        /** @var Field $selectField */
        $selectField = $this->objectManager->create(Field::class);
        $selectField->setName('select_field');

        $fieldsConfig->setFields([$textField, $selectField]);
        $fieldsConfig->setFilters($this->getFiltersConfig());

        return $fieldsConfig;
    }

    protected function getFiltersConfig(): array
    {
        $fromField = $this->objectManager->create(FieldFilter::class);
        $fromField->setField('date_field');
        $fromField->setCondition('gteq');
        $dateFromFilter = $this->objectManager->create(DateFilterConfig::class);
        $dateFromFilter->setValue('01/02/2020');
        $fromField->getExtensionAttributes()->setDateFilter($dateFromFilter);

        $toField = $this->objectManager->create(FieldFilter::class);
        $toField->setField('date_field');
        $toField->setCondition('lt');
        $dateToFilter = $this->objectManager->create(DateFilterConfig::class);
        $dateToFilter->setValue('01/28/2020');
        $toField->getExtensionAttributes()->setDateFilter($dateToFilter);

        return [$fromField, $toField];
    }
}
