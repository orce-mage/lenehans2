<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool\DataProvider;

use Amasty\GiftCard\Model\CodePool\Repository;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var array
     */
    private $loadedData;

    public function __construct(
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Repository $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $data = parent::getData();

        if (!$data['totalRecords']) {
            return [];
        }
        $codePoolId = (int)$data['items'][0][CodePoolInterface::CODE_POOL_ID];
        $codePool = $this->repository->getById($codePoolId);
        $this->loadedData[$codePoolId] = $this->prepareData($codePool->getData());

        $data = $this->dataPersistor->get(\Amasty\GiftCard\Model\CodePool\CodePool::DATA_PERSISTOR_KEY);

        if (!empty($data)) {
            $codePool = $this->repository->getEmptyCodePoolModel();
            $codePool->setData($data);
            $this->loadedData[$codePool->getId()] = $codePool->getData();
            $this->dataPersistor->clear(\Amasty\GiftCard\Model\CodePool\CodePool::DATA_PERSISTOR_KEY);
        }

        return $this->loadedData;
    }

    /**
     * @param array $codePoolData
     *
     * @return array
     */
    private function prepareData(array $codePoolData): array
    {
        $data = [];
        $data['general'] = [
            CodePoolInterface::CODE_POOL_ID => $codePoolData[CodePoolInterface::CODE_POOL_ID],
            CodePoolInterface::TITLE => $codePoolData[CodePoolInterface::TITLE]
        ];
        $data['rule'] = $codePoolData[CodePoolInterface::CODE_POOL_RULE]->getConditionsSerialized();
        $data['codes'][CodePoolInterface::TEMPLATE] = $codePoolData[CodePoolInterface::TEMPLATE];

        return $data;
    }
}
