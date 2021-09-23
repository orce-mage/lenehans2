<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

// @codingStandardsIgnoreFile

namespace Magetrend\Email\Plugin\Backend\Block\Widget;

use \Magento\Backend\Block\Widget\Grid;

class GridPlugin
{
    /**
     * @var \Magetrend\Email\Helper\Data
     */
    protected $_mtHelper;

    /**
     * GridPlugin constructor.
     * @param \Magetrend\Email\Helper\Data $helper
     */
    public function __construct(
        \Magetrend\Email\Helper\Data $helper
    ) {
        $this->_mtHelper = $helper;
    }

    /**
     * Update email template grid
     * @param Grid $grid
     */
    public function beforeToHtml(Grid $grid)
    {
        if ($grid->getId() == 'systemEmailTemplateGrid' && $this->_mtHelper->isActive()) {
            $this->updateEmailTemplateGrid($grid);
        }
    }

    /**
     * Add is MT Email template column to grid
     * @param Grid $gird
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function updateEmailTemplateGrid(Grid $gird)
    {
        $columnBlock = $gird->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Grid\Column')
            ->setData(
                [
                    'header' => __('MT Email'),
                    'index' => 'is_mt_email',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                    'filter' => 'Magetrend\Email\Block\Adminhtml\Email\Template\Grid\Column\Filter\Yesno',
                    'renderer' => 'Magetrend\Email\Block\Adminhtml\Email\Template\Grid\Column\Renderer\Yesno',

                ]
            );

        $columnSet = $gird->getColumnSet();
        $columnSet->insert(
            $columnBlock,
            'type',
            true
        );

        return true;
    }
}
