<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class NewBlock extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    protected $_emailConfig = null;

    public function execute()
    {
        $templateId = $this->getRequest()->getParam('template_id');
        $newBlockId = $this->getRequest()->getParam('block_id');
        $content = $this->getRequest()->getParam('content');
        $template = $this->_initTemplate('template_id');

        if (!$this->_coreRegistry->registry('mt_editor_edit_mode')) {
            $this->_coreRegistry->register('mt_editor_edit_mode', 1);
        }

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $newBlockId = $this->_objectManager->get('Magetrend\Email\Helper\Data')->getUniqueBlockId();
                $blockContent = $this->_objectManager->get('Magetrend\Email\Model\Template')
                    ->createNewBock($template, $newBlockId, $content);

                return $this->_jsonResponse([
                    'success' => 1,
                    'newBlockId' => $newBlockId,
                    'block' => $blockContent,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
