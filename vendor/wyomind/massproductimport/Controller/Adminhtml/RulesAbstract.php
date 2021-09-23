<?php

namespace Wyomind\MassProductImport\Controller\Adminhtml;

/**
 * Class Profiles
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml
 */
abstract class RulesAbstract extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public $_resultForwardFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $_resultRedirectFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    public $_resultRawFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $_resultPageFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryListFactory
     */
    public $directoryListFactory;
    /**
     * @var \Wyomind\MassProductImport\Model\ReplacementFactory
     */
    public $replacementFactory;
    /**
     * @var \Wyomind\MassProductImport\Model\RulesFactory
     */
    public $rulesFactory;
    /**
     * @var \Wyomind\MassProductImport\Model\ResourceModel\ReplacementFactory
     */
    public $replacementResource;


    /**
     * AbstractController constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryListFactory
     * @param \Wyomind\MassProductImport\Model\ReplacementFactory $replacementFactory
     * @param \Wyomind\MassProductImport\Model\RulesFactory $rulesFactory
     * @param \Wyomind\MassProductImport\Model\ResourceModel\ReplacementFactory $ReplacementResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryListFactory,
        \Wyomind\MassProductImport\Model\ReplacementFactory $replacementFactory,
        \Wyomind\MassProductImport\Model\RulesFactory $rulesFactory,
        \Wyomind\MassProductImport\Model\ResourceModel\ReplacementFactory $ReplacementResource
    ) {
        parent::__construct($context);


        $this->_resultForwardFactory=$resultForwardFactory;
        $this->_resultRedirectFactory=$context->getResultRedirectFactory();
        $this->_resultRawFactory=$resultRawFactory;
        $this->_resultPageFactory=$resultPageFactory;
        $this->coreRegistry=$coreRegistry;
        $this->directoryListFactory=$directoryListFactory;
        $this->replacementFactory=$replacementFactory;
        $this->rulesFactory=$rulesFactory;
        $this->replacementResource=$ReplacementResource;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_MassProductImport::rules');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    abstract public function execute();
}
