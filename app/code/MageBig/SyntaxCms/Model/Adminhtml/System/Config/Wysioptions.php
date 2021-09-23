<?php
namespace MageBig\SyntaxCms\Model\Adminhtml\System\Config;

/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Wysioptions
 */
class Wysioptions extends Value
{
    /**
     * Wysioptions constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = (array)$this->getValue();
        $result = [];
        foreach ($value as $data) {
            if (empty($data['name'])) {
                continue;
            }
            $result[] = $data;
        }
        $this->setValue(json_encode($result));
        return $this;
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        $value = json_decode($this->getValue(), true);
        if (is_array($value)) {
            $this->setValue($value);
        }
        return $this;
    }
}
