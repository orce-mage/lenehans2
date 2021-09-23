<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\WidgetPlus\Controller\Adminhtml\Widget;

class Products extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Products chooser Action (Ajax request).
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $productTypeId = $this->getRequest()->getParam('product_type_id', '');
        $chooser = $this->_view->getLayout()->createBlock(
            'MageBig\WidgetPlus\Block\Adminhtml\Widget\Product\Chooser'
        )->setName(
            $this->mathRandom->getUniqueHash('products_grid_')
        )->setUseMassaction(
            true
        )->setProductTypeId(
            $productTypeId
        )->setSelectedProducts(
            explode(',', $selected)
        );
        /* @var $serializer \Magento\Backend\Block\Widget\Grid\Serializer */
        $serializer = $this->_view->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Grid\Serializer',
            '',
            [
                'data' => [
                    'grid_block' => $chooser,
                    'callback' => 'getSelectedProducts',
                    'input_element_name' => 'selectedproducts',
                    'reload_param_name' => 'selectedproducts',
                ],
            ]
        );
        $this->setBody($chooser->toHtml().$serializer->toHtml());
    }
}
