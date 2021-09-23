<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\Filesystem;
use \Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Installation script class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class InstallSchema implements InstallSchemaInterface
{
    const MODULE_MEDIA = 'code/Magetrend/PdfTemplates/view/adminhtml/web/images/media/';

    private $templateColumns = [
        'entity_id'     => ['type' => Table::TYPE_INTEGER,  'length'=> 10, 'primary' => 1],
        'store_id'      => ['type' => Table::TYPE_TEXT,     'length'=> null],
        'type'          => ['type' => Table::TYPE_TEXT,     'length'=> 20],
        'locale'        => ['type' => Table::TYPE_TEXT,     'length'=> 20],
        'name'          => ['type' => Table::TYPE_TEXT,     'length'=> 255],
        'design'        => ['type' => Table::TYPE_TEXT,     'length'=> null],
        'size'          => ['type' => Table::TYPE_TEXT,     'length'=> 50],
        'ppi'           => ['type' => Table::TYPE_INTEGER,  'length'=> 5],
        'hide_overflow' => ['type' => Table::TYPE_INTEGER,  'length'=> 1],
        'footer_height' => ['type' => Table::TYPE_TEXT,  'length'=> 6],
        'header_height' => ['type' => Table::TYPE_TEXT,  'length'=> 6],
        'updated_at'    => [
            'type' => Table::TYPE_TIMESTAMP,
            'length'=> 1,
            'options' => [
                'nullable' => false,
                'default' => Table::TIMESTAMP_UPDATE
            ]
        ],

    ];

    private $elementColumns = [
        'entity_id'     => ['type' => Table::TYPE_INTEGER,  'length'=> 10, 'primary' => 1],
        'template_id'   => ['type' => Table::TYPE_INTEGER,  'length'=> 10],
        'page_id'       => ['type' => Table::TYPE_INTEGER,  'length'=> 3],
        'type'          => ['type' => Table::TYPE_TEXT,     'length'=> 50],
        'uid'           => ['type' => Table::TYPE_TEXT,     'length'=> 50],
        'sort_order'    => ['type' => Table::TYPE_INTEGER,  'length'=> 10],
    ];

    private $attributeColumns = [
        'entity_id'         => ['type' => Table::TYPE_INTEGER,  'length'=> 10, 'primary' => 1],
        'element_id'        => ['type' => Table::TYPE_INTEGER,  'length'=> 10],
        'attribute_key'     => ['type' => Table::TYPE_TEXT,     'length'=> 255],
        'attribute_value'   => ['type' => Table::TYPE_TEXT,     'length'=> null],
    ];

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $io;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var ReadFactory
     */
    public $readFactory;

    /**
     * InstallSchema constructor.
     * @param File $io
     * @param DirectoryList $directoryList
     */
    public function __construct(
        File $io,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        ReadFactory $readFactory
    ) {
        $this->io = $io;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->readFactory = $readFactory;
    }

    /**
     * Installation script
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    //@codingStandardsIgnoreLine
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createTable(
            $installer,
            $installer->getTable('mt_pdftemplates_template'),
            $this->templateColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_pdftemplates_element'),
            $this->elementColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_pdftemplates_attribute'),
            $this->attributeColumns
        );

        $this->createDirectories();
        $installer->endSetup();
    }

    /**
     * Create database table
     *
     * @param $installer
     * @param $tableName
     * @param $columns
     */
    public function createTable($installer, $tableName, $columns)
    {
        $db = $installer->getConnection();
        $table = $db->newTable($tableName);
        
        foreach ($columns as $name => $info) {
            $options = [];
            if (isset($info['options'])) {
                $options = $info['options'];
            }

            if (isset($info['primary']) && $info['primary'] == 1) {
                $options = ['identity' => true, 'nullable' => false, 'primary' => true];
            }

            $table->addColumn(
                $name,
                $info['type'],
                $info['length'],
                $options,
                $name
            );

            if (isset($info['index'])) {
                $table->addIndex(
                    $installer->getIdxName($tableName, [$name]),
                    [$name]
                );
            }
        }

        $db->createTable($table);
    }

    public function createDirectories()
    {
        $this->io->mkdir($this->directoryList->getPath('media').'/pdftemplates', 0775);
    }
}
