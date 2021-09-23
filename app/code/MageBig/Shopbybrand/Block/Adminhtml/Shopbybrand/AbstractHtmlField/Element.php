<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Block\Adminhtml\Shopbybrand\AbstractHtmlField;

class Element extends \MageBig\Shopbybrand\Block\Adminhtml\Shopbybrand\AbstractHtmlField
{
	/**
     * Initialize block template
     */
    protected $_template = 'Magento_Catalog::catalog/form/renderer/fieldset/element.phtml';
    /**
     * Retrieve element label html
     *
     * @return string
     */
    public function getElementLabelHtml()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if (!empty($label)) {
            $element->setLabel(__($label));
        }
        return $element->getLabelHtml();
    }

    /**
     * Retrieve element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->getElement()->getElementHtml();
    }


}
