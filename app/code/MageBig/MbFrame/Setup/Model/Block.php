<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Setup\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Block.
 */
class Block
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Block\Converter
     */
    protected $converter;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param Block\Converter $converter
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        Block\Converter $converter,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->blockFactory = $blockFactory;
        $this->converter = $converter;
        $this->categoryRepository = $categoryRepository;
        $this->blockCollectionFactory = $blockCollectionFactory;
    }

    public function export()
    {
        $path = dirname(dirname(__DIR__)) . '/datacms';
        $list = [
            ['title', 'identifier', 'content'],
        ];

        $blockCollection = $this->blockCollectionFactory->create();
        $blockCollection->addFieldToSelect('*');
        foreach ($blockCollection as $block) {
            $data = [];
            foreach ($list[0] as $attribute) {
                $data[] = $block->getData($attribute);
                if ($attribute == 'identifier') {
                    echo 'id: ' . $block->getData($attribute) . '<br/>';
                }
            }
            $list[] = $data;
        }

        $fp = fopen($path . '/blocks.csv', 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
        echo 'export Block finish' . '<br/><br/>';
    }

    /**
     * @param bool $override
     */
    public function install($override = false)
    {
        //$logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        try {
            $fileName = dirname(dirname(__DIR__)) . '/datacms/blocks.csv';
            //$fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                return;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;

                $blockCollection = $this->blockCollectionFactory->create();
                $oldBlocks = $blockCollection->addFilter('identifier', $row['identifier'])->load();

                if ($override && $oldBlocks) {
                    foreach ($oldBlocks as $oldBlock) {
                        $oldBlock->delete();
                    }
                }

                $data = $this->converter->convertRow($row);
                $cmsBlock = $this->saveCmsBlock($data['block']);
                $cmsBlock->unsetData();
            }

            //$logger->info('Block Imported');
        } catch (\Exception $e) {
            //$logger->critical($e->getMessage());
        }
    }

    /**
     * @param array $data
     *
     * @return \Magento\Cms\Model\Block
     */
    protected function saveCmsBlock($data)
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }
        $cmsBlock->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID]);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();

        return $cmsBlock;
    }

    /**
     * @param string $blockId
     * @param string $categoryId
     */
    protected function setCategoryLandingPage($blockId, $categoryId)
    {
        $categoryCms = [
            'landing_page' => $blockId,
            'display_mode' => 'PRODUCTS_AND_PAGE',
        ];
        if (!empty($categoryId)) {
            $category = $this->categoryRepository->get($categoryId);
            $category->setData($categoryCms);
            $this->categoryRepository->save($categoryId);
        }
    }
}
