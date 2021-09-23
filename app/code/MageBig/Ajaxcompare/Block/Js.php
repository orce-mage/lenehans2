<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\Ajaxcompare\Block;

use Magento\Framework\View\Element\Template\Context;
use MageBig\Ajaxcompare\Helper\Data;

class Js extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'js/main.phtml';

    protected $_ajaxCompareHelper;

    public function __construct(
        Context $context,
        Data $ajaxCompareHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_ajaxCompareHelper = $ajaxCompareHelper;
    }

    public function getAjaxCompareInitOptions()
    {
        return $this->_ajaxCompareHelper->getAjaxCompareInitOptions();
    }
}