<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class GetTemplateList extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        $designCode = $this->getRequest()->getParam('design');
        if (empty($designCode)) {
            return $this->_error(__('Ops... Bad Request'));
        } else {
            try {
                $templateList = $this->templateManager->getTemplateList($designCode);
                return $this->_jsonResponse([
                    'success' => 1,
                    'data' => $templateList
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
