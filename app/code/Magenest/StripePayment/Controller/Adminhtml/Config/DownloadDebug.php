<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Adminhtml\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

class DownloadDebug extends \Magento\Backend\App\Action
{
    protected $directory_list;
    protected $fileFactory;

    public function __construct(
        Action\Context $context,
        DirectoryList $directory_list,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->directory_list = $directory_list;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $version = $this->getRequest()->getParam('version');
        $filename = "stripe_debugfile_".$version."_".time().".log";
        $file = $this->directory_list->getPath("var")."/log/stripe/debug.log";
        if (file_exists($file)) {
            return $this->fileFactory->create($filename, file_get_contents($file), "tmp");
        } else {
            return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
        }
    }
}
