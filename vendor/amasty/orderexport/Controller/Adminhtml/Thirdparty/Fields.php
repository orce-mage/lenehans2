<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\Thirdparty;

class Fields extends \Amasty\Orderexport\Controller\Adminhtml\Thirdparty
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // load&store OrderExport Profile
        $model = $this->_objectManager->create('Amasty\Orderexport\Model\Thirdparty');
        $this->_coreRegistry->register('current_amasty_orderexport', $model);

        /** @var \Amasty\Orderexport\Block\Adminhtml\Thirdparty\Edit\Options\Map $model */
        $model = $this->_objectManager->create('Amasty\Orderexport\Block\Adminhtml\Thirdparty\Edit\Options\Map');
        $table    = $this->getRequest()->getParam('table');
        $model->setTableName($table);
        $model->setTableAlias($table);

        $result = $model->toHtml();
    }
}
