<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Block\System;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use MageBig\SocialLogin\Helper\Social as SocialHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Redirect
 *
 * @package MageBig\SocialLogin\Block\System
 */
class RedirectUrl extends FormField
{
    /**
     * @type SocialHelper
     */
    protected $socialHelper;

    /**
     * RedirectUrl constructor.
     * @param Context $context
     * @param SocialHelper $socialHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SocialHelper $socialHelper,
        array $data = []
    )
    {
        $this->socialHelper = $socialHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $redirectUrl = $this->socialHelper->getCallbackUrl();
        $html = '<input style="opacity:1; cursor:default;" readonly id="' . $element->getHtmlId() . '" class="input-text admin__control-text" value="' . $redirectUrl . '" onclick="this.select()" type="text">';

        return $html;
    }
}
