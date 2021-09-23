<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\CustomEntity;

use Magento\Framework\Model\ResourceModel\Db\Context;

class CustomResourceModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    private $tableName;

    private $idField;

    public function __construct(
        $tableName,
        $idField,
        Context $context,
        $connectionName = null
    ) {
        $this->tableName = $tableName;
        $this->idField = $idField;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init($this->tableName, $this->idField);
    }
}
