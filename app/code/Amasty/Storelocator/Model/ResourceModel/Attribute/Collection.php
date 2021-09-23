<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Attribute;

use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Main;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Base\Model\Serializer $serializer,
        StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init(
            \Amasty\Storelocator\Model\Attribute::class,
            \Amasty\Storelocator\Model\ResourceModel\Attribute::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinAttributes($types)
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (!isset($fromPart['option'])) {
            $this->getSelect()
                ->joinLeft(
                    ['option' => $this->getTable('amasty_amlocator_attribute_option')],
                    'main_table.attribute_id = option.attribute_id',
                    [
                        'attribute_id'       => 'main_table.attribute_id',
                        'options_serialized' => 'option.options_serialized',
                        'value_id'           => 'option.value_id'
                    ]
                )
                ->order("option.sort_order");
            $this->addFieldToFilter('main_table.frontend_input', ['in' => $types]);
        }

        return $this;
    }

    public function getAttributes()
    {
        $connection = $this->getConnection();
        $select = $this->getSelect();

        return $connection->fetchAll($select);
    }

    public function preparedAttributes($allAttributes = false)
    {
        $storeId = $this->storeManager->getStore(true)->getId();

        // remove text field from widget and filter
        $types = $allAttributes ? Main::ALLOWED_ATTRIBUTES : Main::FILTER_ATTRIBUTES;

        $attrAsArray = $this->joinAttributes($types)->getAttributes();

        $attributes = [];

        foreach ($attrAsArray as $attribute) {
            $attributeId = $attribute['attribute_id'];
            if (!array_key_exists($attributeId, $attributes)) {
                $attrLabel = $attribute['frontend_label'];
                $labels = $this->serializer->unserialize($attribute['label_serialized']);
                if (isset($labels[$storeId]) && $labels[$storeId]) {
                    $attrLabel = $labels[$storeId];
                }
                $attributes[$attributeId] = [
                    'attribute_id' => $attributeId,
                    'label' => $attrLabel,
                    'options' => [],
                    'frontend_input' => $attribute['frontend_input'],
                    'attribute_code' => $attribute['attribute_code']
                ];
            }

            if ($attribute['frontend_input'] == 'boolean') {
                $attributes[$attributeId]['options'][] = [
                    'value' => 0,
                    'label' =>  __('No')->getText()
                ];
                $attributes[$attributeId]['options'][] = [
                    'value' => 1,
                    'label' =>  __('Yes')->getText()
                ];
            } else {
                $options = $this->serializer->unserialize($attribute['options_serialized']);
                if ($options) {
                    $optionLabel = $options[0];
                    if (isset($options[$storeId]) && $options[$storeId]) {
                        $optionLabel = $options[$storeId];
                    }
                    $attributes[$attributeId]['options'][] = [
                        'value' => $attribute['value_id'],
                        'label' => $optionLabel
                    ];
                }
            }
        }

        return $attributes;
    }
}
