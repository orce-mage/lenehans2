<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code\DataProvider;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Model\Code\ResourceModel\Grid\CollectionFactory;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    /**
     * @var SearchCriteria
     */
    private $searchCriteria;

    public function __construct(
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create()
            ->addCodePoolField((int)$request->getParam(CodePoolInterface::CODE_POOL_ID));
        $this->searchCriteria = $searchCriteriaBuilder->create()->setRequestName($name);
        $this->collection->setSearchCriteria($this->searchCriteria);
    }

    /**
     * @return SearchCriteria
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }
}
