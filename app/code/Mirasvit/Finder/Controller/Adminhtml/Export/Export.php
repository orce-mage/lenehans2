<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\IndexRepository;

class Export extends Action implements ActionInterface
{
    private $filterRepository;

    private $indexRepository;

    public function __construct(
        FilterRepository $filterRepository,
        IndexRepository $indexRepository,
        Context $context
    ) {
        $this->filterRepository  = $filterRepository;
        $this->indexRepository   = $indexRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $text = $this->export();

        $filename = $this->getFilename();

        $resultPage->setHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv');
        $resultPage->setHeader('Content-length', strlen($text));
        $resultPage->setHeader('Content-type', 'text/csv');
        $resultPage->setContents($text);

        return $resultPage;
    }

    private function getFilename(): string
    {
        $finderId = (int)$this->getRequest()->getParam('finder_id');

        return 'export_finder_' . $finderId;
    }

    private function export(): string
    {
        $text     = '';
        $selected = $this->getRequest()->getParam('selected');
        $finderId = (int)$this->getRequest()->getParam('finder_id');

        if (!$finderId) {
            return $text;
        }

        $filters = $this->filterRepository->getCollection()
            ->addFieldToFilter(FilterInterface::FINDER_ID, $finderId)
            ->addFieldToFilter(FilterInterface::LINK_TYPE, FilterInterface::LINK_TYPE_CUSTOM);

        $filtersNumber = $filters->count();

        $indexes = $this->indexRepository->getCollection()
            ->addFieldToFilter('main_table.' . IndexInterface::FINDER_ID, $finderId);

        $indexes->getSelect()
            ->joinInner(
                ['pe' => $filters->getTable('catalog_product_entity')],
                'main_table.product_id = pe.entity_id',
                'sku'
            )
            ->joinInner(
                ['ff' => $filters->getTable(FilterInterface::TABLE_NAME)],
                'main_table.filter_id = ff.filter_id',
                'position'
            )
            ->joinInner(
                ['fo' => $filters->getTable(FilterOptionInterface::TABLE_NAME)],
                'main_table.option_id = fo.option_id',
                ['value' => 'name']
            )
            ->order([IndexInterface::PRODUCT_ID, FilterInterface::POSITION]);

        if (is_array($selected)) {
            $indexes->addFieldToFilter(IndexInterface::PRODUCT_ID, ['in' => $selected]);
        }

        $row = [];
        if ($indexes->count()) {
            $firstRow = $indexes->getFirstItem();

            $productId = $firstRow->getProductId();
            $row       = array_fill(1, $filtersNumber, '');

            $row[0] = '"' . $firstRow->getSku() . '"';
        }

        foreach ($indexes as $index) {
            if ($productId != $index->getProductId()) {
                ksort($row);
                $text .= implode(',', $row) . PHP_EOL;

                $productId = $index->getProductId();
                $row       = array_fill(1, $filtersNumber, '');
                $row[0]    = '"' . $index->getSku() . '"';
            }

            $row[$index->getPosition()] = '"' . $index->getValue() . '"';
        }

        ksort($row);
        $text .= implode(',', $row) . PHP_EOL;

        return $text;
    }
}
