<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassProductImport\Model\Replacement;

/**
 * Class DataProvider
 * @package Wyomind\MassProductImport\Model\MappingValues
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    public $loadedData;
    /**
     * @var \Wyomind\MassProductImport\Model\ResourceModel\Replacement\Collection
     */
    protected $collection;
    public function __construct(\Wyomind\MassProductImport\Helper\Delegate $wyomind, $name, $primaryFieldName, $requestFieldName, \Wyomind\MassProductImport\Model\ResourceModel\Replacement\CollectionFactory $collectionFactory, array $meta = [], array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $rulesId = $this->request->getParam('id');
        $collection = $this->collection->getCollectionByRuleId($rulesId);
        $rules = $this->rulesModel->load($rulesId);
        foreach ($collection as $item) {
            $this->loadedData[$rulesId]['general']['replacement_container'][] = $item->getData();
        }
        $this->loadedData[$rulesId]['general']['id'] = $rules->getId();
        $this->loadedData[$rulesId]['general']['name'] = $rules->getName();
        $this->loadedData[$rulesId]['general']['use_regexp'] = $rules->getUseRegexp();
        return $this->loadedData;
    }
}