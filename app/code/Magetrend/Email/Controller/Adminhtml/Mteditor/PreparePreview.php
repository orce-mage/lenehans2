<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class PreparePreview extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        $vars = $this->getRequest()->getParam('vars');
        $css = $this->getRequest()->getParam('css');
        $content = $this->getRequest()->getParam('content');
        $templateId = $this->getRequest()->getParam('id');
        $template = $this->_initTemplate('id');

        $vars = $this->jsonHelper->jsonDecode($vars);
        $css = $this->jsonHelper->jsonDecode($css);
        $content = $this->jsonHelper->jsonDecode($content);

        if (!$this->_coreRegistry->registry('mt_editor_preview_mode')) {
            $this->_coreRegistry->register('mt_editor_preview_mode', 1);
        }

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $data = $this->_objectManager->get('Magetrend\Email\Model\Template')
                    ->preparePreview($template, $content, $vars, $css);

                $this->_session->setData('mteditor_preview', base64_encode(json_encode($data)));

                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
