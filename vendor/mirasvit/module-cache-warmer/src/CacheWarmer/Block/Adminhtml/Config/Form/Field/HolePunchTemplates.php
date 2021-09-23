<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Mirasvit\CacheWarmer\Api\Service\BlockMarkServiceInterface;

class HolePunchTemplates extends AbstractFieldArray
{
    /**
     * @var \Mirasvit\CacheWarmer\Block\Adminhtml\Config\Form\Field\Stores
     */
    protected $optionsRenderer;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_addAfter = false;

        $this->addColumn('template', ['label' => __('Template'), 'class' => 'm-hole-punch-data']);
        $this->addColumn('block', ['label' => __('Block class'), 'class' => 'm-hole-punch-data']);
        $this->addColumn(
            BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE,
            ['label' => __('Cms block ID / Widget code'), 'class' => 'm-hole-punch-data']
        );
        $select = $this->_getOptionsRenderer();
        $this->addColumn('store_id', ['label' => __('Store'), 'renderer' => $select]);

        parent::_construct();
    }

    /**
     * @return \Mirasvit\CacheWarmer\Block\Adminhtml\Config\Form\Field\Stores
     */
    protected function _getOptionsRenderer()
    {
        if (!$this->optionsRenderer) {
            $this->optionsRenderer = $this->getLayout()->createBlock(
                \Mirasvit\CacheWarmer\Block\Adminhtml\Config\Form\Field\Stores::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->optionsRenderer->setClass('customer_options_select');
            $this->optionsRenderer->setExtraParams('style="width:150px; font-size:14px"');
        }

        return $this->optionsRenderer;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        if ($row->getData('store_id')) {
            $options['option_' . $this->_getOptionsRenderer()->calcOptionHash($row->getData('store_id'))]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
