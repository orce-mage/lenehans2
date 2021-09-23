<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml;

/**
 * Class Profiles
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml
 */
abstract class Profiles extends \Wyomind\MassStockUpdate\Controller\Adminhtml\AbstractController
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    public $_coreRegistry = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Config
     */
    public $_configHelper = null;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|null
     */
    public $_directoryRead = null;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $_directoryList;
    /**
     * @var null
     */
    public $_parserHelper = null;
    /**
     * @var \Magento\Framework\App\CacheInterface|null
     */
    public $_cacheManager = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    public $_storeManager = null;
    /**
     * @var null|\Wyomind\Framework\Helper\Module
     */
    public $_framework = null;

    /**
     * Profiles constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Model\Context $contextModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wyomind\MassStockUpdate\Helper\Config $configHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param String $module
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Model\Context $contextModel,
        \Magento\Framework\Registry $coreRegistry,
        \Wyomind\MassStockUpdate\Helper\Config $configHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Wyomind\Framework\Helper\Download $framework
    ) {
    
        $this->_coreRegistry = $coreRegistry;
        $this->_configHelper = $configHelper;
        $this->_cacheManager = $contextModel->getCacheManager();
        $this->_directoryRead = $directoryRead->create("");
        $this->_directoryList = $directoryList;
        $this->_storeManager = $storeManager;
        $this->_framework = $framework;

        parent::__construct($context, $resultForwardFactory, $resultRawFactory, $resultPageFactory);
    }
}
