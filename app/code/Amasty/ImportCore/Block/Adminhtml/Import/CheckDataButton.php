<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Block\Adminhtml\Import;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * @codeCoverageIgnore
 */
class CheckDataButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $entityCode = $this->request->getParam('entity_code');

        if (!$entityCode) {
            return [];
        }

        return [
            'label' => __('Check Data'),
            'class' => 'amimport-check-data primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'index = controls',
                                'actionName' => 'checkData',
                            ]
                        ]
                    ]
                ],
            ],
            'on_click' => '',
            'sort_order' => 60
        ];
    }
}
