<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Registry                  $registry
     *
     * @internal param string $instanceName
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);

        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_coreRegistry       = $registry;
    }

    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->createBlock('Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\History\Grid','amorderexport_grid');
        $resultLayout->getLayout()->addOutputElement('amorderexport_grid');

        return $resultLayout;
    }
}
