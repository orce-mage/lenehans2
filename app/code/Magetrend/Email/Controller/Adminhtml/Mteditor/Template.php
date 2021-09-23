<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class Template extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    public $_emailConfig = null;

    public function execute()
    {
        $template = $this->_initTemplate('id');
        $templateId = $this->getRequest()->getParam('template');
        $locale = $this->getRequest()->getParam('localeCode');
        try {
            if (is_numeric($templateId)) {
                $template->load($templateId);
            } else {
                $template->setForcedArea($templateId);
                $template->loadDefault($templateId);
            }
            $this->getResponse()->clearHeaders();
            $this->getResponse()->setHttpResponseCode(200);

            $template->setData(
                'template_subject',
                $this->_objectManager->get('Magetrend\Email\Model\Varmanager')->prepareValue($template->getTemplateSubject(), 0, $locale)
            );
            return $this->_resultJsonFactory->create()->setData([
                'template' => $template->getData(),
                'newFormKey' =>  $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
            ]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
    }
}
