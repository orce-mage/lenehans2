<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Block\Adminhtml\Product\Edit;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Comment extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry, Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $attribute = $this->registry->registry('entity_attribute');
        if ($attribute && $attribute->getAttributeCode() == 'custom_stock_status') {
            $result = parent::toHtml();
        } else {
            $result = '';
        }

        return $result;
    }
}
