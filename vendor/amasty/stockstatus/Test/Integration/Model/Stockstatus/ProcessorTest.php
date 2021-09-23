<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Test\Integration\Model\Stockstatus;

use Amasty\Stockstatus\Model\Indexer\Rule\ProductProcessor;
use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractIntegrity;

class ProcessorTest extends AbstractIntegrity
{
    /**
     * @var Processor
     */
    private $model;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductProcessor
     */
    private $productIndexProcessor;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(Processor::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepository::class);
        $this->productIndexProcessor = Bootstrap::getObjectManager()->create(ProductProcessor::class);
    }

    /**
     * Testing for processor retrieve stockstatus for product by rules & ranges.
     *
     * @dataProvider executeDataProvider
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store cataloginventory/options/show_out_of_stock 1
     * @magentoDataFixture Amasty_Stockstatus::Test/Integration/_files/products.php
     * @magentoDataFixture Amasty_Stockstatus::Test/Integration/_files/status_options.php
     * @magentoDataFixture Amasty_Stockstatus::Test/Integration/_files/rules.php
     */
    public function testExecute(string $sku, bool $isNull): void
    {
        $product = $this->productRepository->get($sku);
        $this->productIndexProcessor->reindexRow($product->getId());
        $this->model->execute([$product]);

        if ($isNull) {
            $this->assertNull($product->getExtensionAttributes()->getStockstatusInformation()->getStatusId());
        } else {
            $this->assertNotNull($product->getExtensionAttributes()->getStockstatusInformation()->getStatusId());
        }
    }

    public function executeDataProvider(): array
    {
        return [
            ['stockstatus-simple-1', false],
            ['stockstatus-simple-2', false],
            ['stockstatus-simple-3', false],
            ['stockstatus-simple-4', true]
        ];
    }
}
