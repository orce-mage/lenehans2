<?php
/**
 * Copyright © 2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageBig\WidgetPlus\Block\Adminhtml\Block\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const URL_PATH_EDIT = 'cms/block/edit';

    /**
     * Action constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $href = $this->_urlBuilder->getUrl(static::URL_PATH_EDIT, [
            'block_id' => $row->getData('block_id'),
        ]);
        return '<a href="' . $href . '" target="_blank">' . __('Edit') . '</a>';
    }
}
