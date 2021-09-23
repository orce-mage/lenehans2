<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class Save extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    protected $_emailConfig = null;

    public function execute()
    {
        $vars = $this->getRequest()->getParam('vars');
        $css = $this->getRequest()->getParam('css');
        $templateContent = $this->getRequest()->getParam('template_content');
        $templateId = $this->getRequest()->getParam('template_id');
        $applyToAll = $this->getRequest()->getParam('apply_to_all');
        $removedBlockList = $this->getRequest()->getParam('removed_block_list');
        $template = $this->_initTemplate();

        $vars = $this->jsonHelper->jsonDecode($vars);
        $css = $this->jsonHelper->jsonDecode($css);
        $templateContent = $this->jsonHelper->jsonDecode($templateContent);

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $templateModel = $this->_objectManager->get('Magetrend\Email\Model\Template');
                $templateModel->saveTemplate($templateId, $templateContent, $vars, $css, $applyToAll);
                $templateModel->deleteBlock($templateId, $removedBlockList);

                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
