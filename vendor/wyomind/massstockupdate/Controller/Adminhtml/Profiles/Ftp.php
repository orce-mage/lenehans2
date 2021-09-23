<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Ftp
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Ftp extends \Magento\Backend\App\Action
{

    /**
     * @var \Wyomind\MassStockUpdate\Helper\Ftp
     */
    public $_ftpHelper;

    /**
     * Ftp constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Wyomind\MassStockUpdate\Helper\Ftp $ftpHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Wyomind\MassStockUpdate\Helper\Ftp $ftpHelper
    ) {
    

        parent::__construct($context);
        $this->_ftpHelper = $ftpHelper;
    }

    /**
     *
     */
    public function execute()
    {

        try {
            $data = $this->getRequest()->getParams();
            $ftp = $this->_ftpHelper->getConnection($data);

            $content = __("Connection succeeded");
            $ftp->close();
        } catch (\Exception $e) {
            $content = $e->getMessage();
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($content));
    }
}
