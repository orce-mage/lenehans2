<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Thirdparty\Edit\Options;

class Map extends \Magento\Backend\Block\Widget\Form\Generic
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

	private $fields;
	private $fieldsTable;
	private $fieldsTableAlias;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Framework\Registry             $registry
	 * @param \Magento\Framework\Data\FormFactory     $formFactory
	 * @param \Amasty\Orderexport\Helper\Fields       $helperFields
	 * @param array                                   $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\FormFactory $formFactory,
		\Amasty\Orderexport\Helper\Fields $helperFields,
		array $data = []
	) {
		$this->_coreRegistry = $registry;
		$this->_formFactory  = $formFactory;
		$this->_helperFields = $helperFields;
		$this->_init();
		parent::__construct($context, $registry, $formFactory);
	}

	private function _init()
	{
		$this->fieldsTable      = $this->getTableName() ?: $this->_helperFields->getOrderFieldsTable();
		$this->fieldsTableAlias = $this->getTableAlias() ?: $this->_helperFields->getOrderFieldsTableAlias();
		$this->fields           = $this->getTableFields($this->fieldsTable);
	}

	private function getTableFields($tableName)
	{
		$tableInfo = $this->_helperFields->getFields($tableName, $tableName);

		return $tableInfo;
	}

	public function getOptionValues()
	{
		$values = $this->getData('mapping');
		$values = $values ? @unserialize($values) : [];

		return $values;
	}

	public function getSelectOptionsHtml($selected = '')
	{
		$this->_init();

		$html = '';
		foreach ($this->fields as $field) {
			$fieldValue = $this->fieldsTableAlias . '.' . $field;
			$html
				.= '<option value="' . $fieldValue . '" ' . ($selected == $fieldValue ? 'selected="selected"' : '') . '>
                ' . $this->fieldsTable . '.' . $field . '
                ' . '</option>' . "\r\n";
		}

		return $html;
	}

	public function getFields()
	{
		return $this->fields;
	}
}
