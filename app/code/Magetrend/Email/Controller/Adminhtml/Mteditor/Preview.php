<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class Preview extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        try {
            if ($this->getRequest()->getParam('id')) {
                $template = $this->_initTemplate('id');
                $template->setForcedArea($template->getData('orig_template_code'));

                $this->_coreRegistry->register('mteditor_preview_content', [
                    'content' => $template->getProcessedTemplate(
                        $this->_objectManager->get('Magetrend\Email\Helper\Data')->getDemoVars($template)
                    ),
                    'css' => $template->getTemplateStyles()
                ]);
            } elseif ($this->_session->getData('mteditor_preview')) {
                $data = $this->_session->getData('mteditor_preview');
                //@codingStandardsIgnoreStart
                $this->_coreRegistry->register('mteditor_preview_content', json_decode(base64_decode($data), true));
                //@codingStandardsIgnoreEnd
            }

            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Preview'));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred. The email template can not be opened for preview. '. $e->getMessage())
            );
            $this->_redirect('adminhtml/*/');
        }
    }
}
