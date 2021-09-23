<?php

namespace MageBig\WysiwygFiles\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Filetypes
 */
class Filetypes extends AbstractFieldArray
{

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'extension',
            [
                'label'     => __('Allowed Filetype'),
            ]
        );
		$this->_addAfter = false;
    }

}
