<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Model\ResourceModel;

use Magento\Framework\DataObject;

class SelectedBrands extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_date;
	/**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		$connectionName = null
	) {
		parent::__construct($context, $connectionName);
		$this->_date = $date;
	}

	protected function _construct()
	{
		$this->_init('eav_attribute_option', 'option_id');
	}

	public function getAttributeCodeList()
    {
		$select = $this->getConnection()->select();
		$mainTable = $this->getTable('catalog_eav_attribute');
		$eavAttributeTable = $this->getTable('eav_attribute');
		$select->from(['maintable' => $mainTable])
			->joinLeft( [ 'ea' => $eavAttributeTable ], 'maintable.attribute_id = ea.attribute_id', ['attribute_code', 'frontend_label'])
			->where('ea.frontend_input = "select"
                    AND ((ea.source_model is NULL) OR (ea.source_model = "Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Table"))'
             );

		$attributes = $this->getConnection()->fetchAll($select);

		$list = [];
		foreach($attributes as $attribute){
			$list[] = ['value' => $attribute['attribute_code'], 'label' => $attribute['frontend_label'] ];
		}
		return $list;
	}
}
