<?php

namespace MageBig\Ajaxwishlist\Block;

use MageBig\Ajaxwishlist\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

class Js extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'js/main.phtml';

    protected $_ajaxwishlistHelper;

    public function __construct(
        Context $context,
        Data $ajaxwishlistHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ajaxwishlistHelper = $ajaxwishlistHelper;
    }

    public function getAjaxWishlistInitOptions()
    {
        return $this->_ajaxwishlistHelper->getAjaxWishlistInitOptions();
    }
}