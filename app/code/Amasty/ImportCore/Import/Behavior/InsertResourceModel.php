<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Behavior;

use Magento\Framework\Model\AbstractModel;

/**
 * You can extend this class in new resource model to insert record with custom increment field.
 * Can be used by Update/AddUpdate behaviors
 */
abstract class InsertResourceModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Prevent auto_increment field check
     *
     * @param AbstractModel $object
     *
     * @return bool
     */
    protected function isObjectNotNew(AbstractModel $object)
    {
        return false;
    }

    /**
     * Save New Record with any auto_increment field
     *
     * @param AbstractModel $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function saveNewObject(AbstractModel $object)
    {
        $bind = $this->_prepareDataForSave($object);

        $this->getConnection()->insert($this->getMainTable(), $bind);

        if ($this->_useIsObjectNew) {
            $object->isObjectNew(false);
        }
    }
}
