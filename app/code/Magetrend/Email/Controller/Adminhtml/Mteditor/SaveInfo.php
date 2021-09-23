<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class SaveInfo extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{

    public function execute()
    {
        $request = $this->getRequest();
        $templateId = $this->getRequest()->getParam('id');
        $templateCode = $request->getParam('template_code');
        $templateSubject = $request->getParam('template_subject');
        $storeId = $request->getParam('store_id');
        $template = $this->_initTemplate('id');

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $template->setTemplateSubject($templateSubject)
                    ->setStoreId($storeId)
                    ->setTemplateCode($templateCode);
                $template->save();

                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
