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

namespace Mirasvit\Finder\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Repository\FilterRepository;

class Example extends Action implements ActionInterface
{
    private $collectionFactory;

    private $filterRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        FilterRepository $filterRepository,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filterRepository  = $filterRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $finderId = (int)$this->getRequest()->getParam('finder_id');

        $text = $this->getSampleText($finderId);

        $resultPage->setHeader('Content-Disposition', 'attachment; filename=import_example.csv');
        $resultPage->setHeader('Content-length', strlen($text));
        $resultPage->setHeader('Content-type', 'text/csv');
        $resultPage->setContents($text);

        return $resultPage;
    }

    private function getSampleText(int $finderId): string
    {
        $filters = $this->filterRepository->getCollection()
            ->addFieldToFilter(FilterInterface::FINDER_ID, $finderId)
            ->addFieldToFilter(FilterInterface::LINK_TYPE, FilterInterface::LINK_TYPE_CUSTOM);

        $productCollection = $this->collectionFactory->create();
        $productCollection->getSelect()->orderRand()->limit(5);

        $text = '';
        foreach ($productCollection as $i => $product) {
            $row = ['"' . $product->getSku() . '"'];

            foreach ($filters as $filter) {
                $row[] = '"' . $filter->getAttributeCode() . ' value ' . $i . '"';
            }

            $text .= implode(',', $row) . PHP_EOL;
        }

        return $text;
    }
}
