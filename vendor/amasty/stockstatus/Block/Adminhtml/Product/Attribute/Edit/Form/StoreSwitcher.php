<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit\Form;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Model\Backend\StockstatusSettings\Form\ParamsProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class StoreSwitcher extends Generic
{
    /**
     * @var ParamsProvider
     */
    private $paramsProvider;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ParamsProvider $paramsProvider
    ) {
        $this->paramsProvider = $paramsProvider;

        parent::__construct(
            $context,
            $registry,
            $formFactory
        );
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'preview_form',
                    'action' => $this->getUrl('amstockstatus/product_attribute/settings', [
                        StockstatusSettingsInterface::OPTION_ID => $this->paramsProvider->getOptionId()
                    ]),
                ],
            ]
        );

        $form->setUseContainer(true);
        $form->addField(
            'preview_selected_store',
            'hidden',
            ['name' => 'store', 'id'=>'preview_selected_store']
        );
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
