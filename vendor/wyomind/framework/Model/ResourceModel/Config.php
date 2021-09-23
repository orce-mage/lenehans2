<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model\ResourceModel;

/**
 * Get the config directly from the database
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Registry $registry,
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Registry $registry,
        $connectionName = null
    ) {
        $this->registry = $registry;
        parent::__construct($context, $connectionName);
    }

    /**
     * Class insternal constructor (unused, be defined because it is an abstract method)
     * @return \Wyomind\Framework\Model\ResourceModel\Config
     */
    public function _construct()
    {
        return $this;
    }

    /**
     * Get a config value for a path (scope default), in the database directly
     * @param string $path
     * @return string | integer
     */
    public function getDefaultValueByPath($path)
    {
        $value = $this->registry->registry($path);
        if ($value == '') {
            $connection = $this->getConnection();
            $result = $connection->select()
                ->from($this->getTable('core_config_data'), ['value'])
                ->where("path = ? and scope_id = 0", $path)
                ->limit(1);
            $value = $connection->fetchOne($result);
            if ($value === false) {
                $value = null;
            }
            if ($value != "") {
                $this->registry->register($path, $value);
            }
        }
        return $value;
    }
}
