<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Controller\Adminhtml\Progress;

/**
 * Class Updater
 * @package Wyomind\Framework\Controller\Adminhtml
 */
class Updater extends \Magento\Backend\App\Action
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {


        $json=[];

        $data=$this->getRequest()->getPost('data');

        foreach ($data as $f) {
            $row=new \Magento\Framework\DataObject;
            $row->setId($f["id"]);
            $field=$f["field"];
            $row->setData($field, $f["cron"]);
            $module=$f["module"];
            $status=$this->_objectManager->create("Wyomind\\" . $module . "\Block\Adminhtml\Progress\Status");
            $json[]=["id"=>$f["id"], "content"=>($status->render($row))];
        }
        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($json));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
