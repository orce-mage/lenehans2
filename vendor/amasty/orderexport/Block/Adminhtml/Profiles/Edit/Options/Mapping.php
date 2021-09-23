<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\Options;

class Mapping extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Amasty\Orderexport\Helper\Fields
     */
    protected $_helperFields;

    /**
     * @var string
     */
    protected $_template = 'options.phtml';

    /** @var \Amasty\Orderexport\Model\ResourceModel\Attribute\CollectionFactory  */
    protected $attributeCollectionFactory;

    private $orderFields;
    private $orderFieldsTable      = 'sales_order';
    private $orderFieldsTableAlias = 'main_table';

    private $orderItemFields;
    private $orderItemFieldsTable      = 'sales_order_item';
    private $orderItemFieldsTableAlias = 'sales_order_item';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Amasty\Orderexport\Helper\Fields $helperFields
     * @param \Amasty\Orderexport\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Orderexport\Helper\Fields $helperFields,
        \Amasty\Orderexport\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry         = $registry;
        $this->_formFactory          = $formFactory;
        $this->_helperFields         = $helperFields;
        $this->orderFields           = $this->getFields($this->orderFieldsTable, $this->orderFieldsTable);
        $this->orderItemFields       = $this->getFields($this->orderItemFieldsTable, $this->orderItemFieldsTable);
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        parent::__construct($context, $registry, $formFactory);
    }

    private function getFields($tableName, $alias)
    {
        $tableInfo = $this->_helperFields->getFields($tableName, $alias);

        return $tableInfo;
    }

    public function getOptionValues()
    {
        $values = $this->getData('field_mapping');
        $values = $values ? @unserialize($values) : [];

        return $values;
    }

    public function getSelectOptionsHtml($selected = '')
    {
        $html = '';
        foreach ($this->orderFields as $orderField) {
            $fieldValue = $this->orderFieldsTableAlias . '.' . $orderField;
            $html
                .= '<option value="' . $fieldValue . '" ' . ($selected == $fieldValue ? 'selected="selected"' : '') . '>
                ' . $this->orderFieldsTable . '.' . $orderField . '
                ' . '</option>' . "\r\n";
        }

        foreach ($this->orderItemFields as $orderField) {
            $fieldValue = $this->orderItemFieldsTableAlias . '.' . $orderField;
            $html
                .= '<option value="' . $fieldValue . '" ' . ($selected == $fieldValue ? 'selected="selected"' : '') . '>
                ' . $this->orderItemFieldsTable . '.' . $orderField . '
                ' . '</option>' . "\r\n";
        }

        $tables = $this->_helperFields->getAllTables();

        foreach ($tables as $alias => $config) {
            $table = $config['table'];
            $fields = $this->_helperFields->getFields($table, $alias, true, true);
            foreach ($fields as $fieldName => $alias) {
                $html
                    .= '<option value="' . $fieldName . '" ' . ($selected == $fieldName ? 'selected="selected"' : '') . '>
                ' . $fieldName . '
                ' . '</option>' . "\r\n";
            }
        }

        return $html;
    }

    public function getOrderFields()
    {
        return $this->orderFields;
    }
}