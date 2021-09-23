<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Controller\Adminhtml\Mtemail;

class Delete extends \Magetrend\Email\Controller\Adminhtml\Mtemail
{
    public function execute()
    {
        try {
            $this->_objectManager->get('Magetrend\Email\Model\Template\Mass')
                ->deleteTemplates($this->getRequest()->getParam('store_id'));

            return $this->_resultJsonFactory->create()->setData([
                'success' => 1,
            ]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_error($e->getMessage());
        }
    }
}
