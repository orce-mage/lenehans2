<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Library
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Library extends \Wyomind\MassStockUpdate\Controller\Adminhtml\AbstractController
{

    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Data
     */
    public $_dataHelper = null;

    /**
     * Library constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Wyomind\MassStockUpdate\Helper\Data $dataHelper
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\MassStockUpdate\Helper\Data $dataHelper
    ) {
    
        parent::__construct($context, $resultForwardFactory, $resultRawFactory, $resultPageFactory);

        $this->_dataHelper = $dataHelper;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $library['error'] = 'false';
            $library['data'] = [];
            $library['color'] = ["rgba(0,0,0,0.1)", "rgba(50,255,0,0.1)", "rgba(50,255,0,0.1)"];
            $library['tag'] = ["Values", "Values", ""];
            $library['header'] = ["Attribute", "Type", "Example"];

            foreach ($this->_dataHelper->getJsonAttributes() as $name => $group) {
                if ($name == "storeviews") {
                    continue;
                }

                foreach ($group as $attribute) {
                    $value = isset($attribute["value"]) ? $attribute["value"] : "-";
                    $library['data'][] = ["<b>" . $name . "</b> | " . $attribute["label"], $attribute["type"], $value];
                }
            }


            return $this->getResponse()->representJson(json_encode($library));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getResponse()->representJson('{"error":"true","message":"' . preg_replace("/\r|\n|\t|\\\\/", "", nl2br(htmlentities($e->getMessage()))) . '"}');
        }
    }
}
