<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Attributes
 */
abstract class Attributes extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Amasty\Storelocator\Model\AttributeFactory
     */
    protected $attributeFactory;
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute
     */
    protected $attributeResourceModel;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $forwardFactory;
    /**
     * @var LoggerInterface
     */
    protected $logInterface;
    /**
     * @var \Amasty\Storelocator\Model\OptionsFactory
     */
    protected $optionsFactory;
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Options
     */
    protected $optionsResourceModel;
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logInterface,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Amasty\Storelocator\Model\AttributeFactory $attributeFactory,
        \Amasty\Storelocator\Model\OptionsFactory $optionsFactory,
        \Amasty\Storelocator\Model\ResourceModel\Attribute $attributeResourceModel,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Storelocator\Model\ResourceModel\Options $optionsResourceModel,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Base\Model\Serializer $serializer,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->session = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        $this->attributeFactory = $attributeFactory;
        $this->attributeResourceModel = $attributeResourceModel;
        $this->coreRegistry = $coreRegistry;
        $this->forwardFactory = $forwardFactory;
        $this->logInterface = $logInterface;
        $this->optionsFactory = $optionsFactory;
        $this->optionsResourceModel = $optionsResourceModel;
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->filter = $filter;
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Storelocator::attributes');
        $resultPage->addBreadcrumb(__('Location Attributes'), __('Location Attributes'));
        $resultPage->getConfig()->getTitle()->prepend(__('Location Attributes'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Storelocator::attributes');
    }
}
