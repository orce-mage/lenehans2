<?php

namespace Amasty\Faq\Controller\Adminhtml\Import;

use Amasty\Base\Controller\Adminhtml\Import\Download as BaseDownload;
use Magento\Backend\App\Action;

class Download extends \Amasty\Faq\Controller\Adminhtml\AbstractImport
{
    /**
     * @var BaseDownload
     */
    private $download;

    public function __construct(BaseDownload $download, Action\Context $context)
    {
        parent::__construct($context);
        $this->download = $download;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        return $this->download->downloadSample('Amasty_Faq');
    }
}
