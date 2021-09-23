<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Controller\Adminhtml\Mtemail;

class Create extends \Magetrend\Email\Controller\Adminhtml\Mtemail
{
    public function execute()
    {
        try {
            $this->_objectManager->get('Magetrend\Email\Model\Template\Mass')
                ->createTemplates($this->getRequest()->getParam('store_id'), $this->getRequest()->getParam('design'));
            return $this->_resultJsonFactory->create()->setData([
                'success' => 1,
                'message' => __('The templates have been created successfully.')
            ]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_error($e->getMessage());
        }
    }
}
