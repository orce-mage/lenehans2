<?php

namespace Amasty\Faq\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

abstract class AbstractDummy extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
