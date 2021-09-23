<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class Create extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    protected $_emailConfig = null;

    public function execute()
    {
        $request = $this->getRequest();
        $templateId = $request->getParam('orig_template_code');
        $locale = $request->getParam('localeCode');
        $subject = $request->getParam('template_subject');
        $name = $request->getParam('template_code');
        $storeId = $request->getParam('store_id');

        try {
            $templateModel = $this->_objectManager->get('Magetrend\Email\Model\Template');
            if (is_numeric($templateId)) {
                $template = $templateModel->copyTemplate($templateId, $name, $subject, $storeId, $locale);
            } else {
                $template = $templateModel->createTemplate($templateId, $name, $subject, $storeId, $locale);
            }

            return $this->_jsonResponse([
                'success' => 1,
                'redirectTo' => $this->getUrl("*/*/index/", ['id' => $template->getId()])
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->_error($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_error($e->getMessage());
        }
    }
}
