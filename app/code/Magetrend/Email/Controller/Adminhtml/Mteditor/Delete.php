<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class Delete extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    
    public function execute()
    {
        $template = $this->_initTemplate('id');

        if (!$template->getId()) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $this->_objectManager->get('Magetrend\Email\Model\Template')->deleteTemplate($template);

                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
