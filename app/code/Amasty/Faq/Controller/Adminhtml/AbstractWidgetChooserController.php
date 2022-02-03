<?php

declare(strict_types=1);

namespace Amasty\Faq\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutFactory;

abstract class AbstractWidgetChooserController extends Action
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var RawFactory
     */
    private $rawResultFactory;

    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        RawFactory $rawResultFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->rawResultFactory = $rawResultFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $uniqId = (string)$this->getRequest()->getParam('uniq_id');
        $layout = $this->layoutFactory->create();
        $faqCategoriesGridBlock = $layout->createBlock(
            $this->getChooserGridClass(),
            '',
            ['data' => ['id' => $uniqId]]
        );
        $rawResult = $this->rawResultFactory->create();

        return $rawResult->setContents($faqCategoriesGridBlock->toHtml());
    }

    /**
     * @return string
     */
    abstract public function getChooserGridClass();
}
