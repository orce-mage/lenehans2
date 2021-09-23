<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Block\Adminhtml\System\Config\Form\Field;

/**
 * Class Encrypted
 * @package Wyomind\Framework\Block\Adminhtml\System\Config\Form\Field
 */
class Encrypted extends \Magento\Config\Block\System\Config\Form\Field
{


    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor = null;

    /**
     * Encrypted constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Wyomind\Framework\Helper\Module $frameworkHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wyomind\Framework\Helper\Module $frameworkHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->frameworkHelper = $frameworkHelper;
        $this->_encryptor = $encryptor;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (version_compare("2.2.1", $this->frameworkHelper->getMagentoVersion()) <= 0) {
            $element->setValue($this->_encryptor->decrypt($element->getValue()));
        }
        return parent::_getElementHtml($element);
    }
}
