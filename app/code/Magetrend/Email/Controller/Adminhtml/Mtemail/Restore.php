<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Controller\Adminhtml\Mtemail;

class Restore extends \Magetrend\Email\Controller\Adminhtml\Mtemail
{
    public function execute()
    {
        try {
            $this->_objectManager->get('Magetrend\Email\Model\Template\Mass')
                ->restoreTemplates($this->getRequest()->getParam('store'));
            
            return $this->_resultJsonFactory->create()->setData([
                'success' => 1,
                'message' => __('Successful! Note: Please clear the cache!')
            ]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_error($e->getMessage());
        }
    }
}
