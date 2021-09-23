<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Block\Adminhtml\Edit\Tab\History\Renderer;

class Origin extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Wyomind\Framework\Helper\History
     */
    protected $historyHelper;

    /**
     * Class constructor
     * @param \Magento\Backend\Block\Context $context
     * @param \Wyomind\Framework\Helper\History $historyHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Wyomind\Framework\Helper\History $historyHelper,
        array $data = []
    ) {
    
        $this->historyHelper = $historyHelper;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->historyHelper->getOriginToString($row->getOrigin());
    }
}
