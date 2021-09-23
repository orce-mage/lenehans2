<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Helper;

/**
 * Class Uploader
 *
 * @package Amasty\Orderexport\Helper
 */
class Uploader extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var  \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;

    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $_cmsBlock;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_modelOrder;

    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_modelOrderInvoice;

    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_modelOrderConfig;


    /**
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\View\Result\PageFactory                   $resultPageFactory
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Framework\Message\ManagerInterface                  $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate
     * @param \Magento\Framework\Filesystem                                $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\View\LayoutFactory                        $layoutFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Framework\App\ActionFlag                            $actionFlag
     * @param \Magento\Catalog\Model\Product                               $product
     * @param \Magento\Framework\App\Helper\Context                        $context
     * @param \Magento\Cms\Model\Page                                      $cmsPage
     * @param \Magento\Sales\Model\Order                                   $modelOrder
     * @param \Magento\Sales\Model\Order\Invoice                           $_modelOrderInvoice
     * @param \Magento\Sales\Model\Order\Config                            $_modelOrderConfig
     * @param \Magento\Cms\Model\Block                                     $cmsBlock
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Sales\Model\Order\Invoice $_modelOrderInvoice,
        \Magento\Sales\Model\Order\Config $_modelOrderConfig,
        \Magento\Cms\Model\Block $cmsBlock
    ) {
        parent::__construct($context);
        $this->_registry                = $registry;
        $this->_resultPageFactory       = $resultPageFactory;
        $this->_objectManager           = $objectManager;
        $this->_messageManager          = $messageManager;
        $this->_localeDate              = $localeDate;
        $this->_filesystem              = $filesystem;
        $this->_storeManager            = $storeManager;
        $this->layoutFactory            = $layoutFactory;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_product                 = $product;
        $this->_actionFlag              = $actionFlag;
        $this->_cmsPage                 = $cmsPage;
        $this->_cmsBlock                = $cmsBlock;
        $this->_modelOrder              = $modelOrder;
        $this->_modelOrderInvoice       = $_modelOrderInvoice;
        $this->_modelOrderConfig        = $_modelOrderConfig;
    }


    /**
     * @param \Amasty\Orderexport\Model\Profiles $profile
     * @param string                             $filePath
     */
    public function uploadFile($profile, $filePath)
    {
        if ($profile->getData('ftp_use')) {
            if ($profile->getType() == 1) {
                $this->uploadBySftp($profile, $filePath);
            } else {
                $this->uploadByFtp($profile, $filePath);
            }
        }
    }


    /**
     * @param \Amasty\Orderexport\Model\Profiles $profile
     * @param string                             $filePath
     */
    private function uploadBySftp($profile, $filePath)
    {
        $connection = false;
        $arrHost    = explode(':', $profile->getFtpHost());
        $ftpHost    = $arrHost[0];
        $ftpPort    = isset($arrHost[1]) ? $arrHost[1] : false;
        if (!$ftpPort) {
            $ftpPort = 22;
        }
        if (!function_exists('ssh2_connect')) {
            $text = 'Please install PECL ssh2 >= 0.9.0.';
            $e    = new \Exception($text);
            $this->_messageManager->addException($e, $text);
        } else {
            $connection = ssh2_connect($ftpHost, $ftpPort);
        }

        if ($connection) {
            $ftpLogin = ssh2_auth_password(
                $connection, $profile->getFtpLogin(),
                $profile->getFtpPassword()
            );
            if ($ftpLogin) {
                $remotePath = $profile->getFtpPath();
                if ('/' != substr($remotePath, -1, 1)
                    && '\\' != substr($remotePath, -1, 1)
                ) {
                    $remotePath .= '/';
                }
                $remoteFileName = substr(
                    $filePath, strrpos($filePath, '/') + 1
                );
                $remotePath .= $remoteFileName;

                $sftp = ssh2_sftp($connection);

                $sftpStream = @fopen('ssh2.sftp://' . $sftp . $remotePath, 'w');

                if (!$sftpStream) {
                    $text = "Could not open remote file: $remotePath";
                    $e    = new \Exception($text);
                    $this->_messageManager->addException($e, $text);
                }
                $data_to_send = @file_get_contents($filePath);

                if ($data_to_send === false) {
                    $e = new \Exception("Could not open local file: {$filePath}.");
                    $this->_messageManager->addException($e, "Could not open local file: {$filePath}.");
                }

                if (@fwrite($sftpStream, $data_to_send) === false) {
                    $text = "Could not send data from file: {$filePath}.";
                    $e    = new \Exception($text);
                    $this->_messageManager->addException($e, $text);
                }

                fclose($sftpStream);
            } else {
                $text = 'Error logging in to the SFTP server.';
                $e    = new \Exception($text);
                $this->_messageManager->addException($e, $text);
            }
        } else {
            $text = "Error connecting to the SFTP server.";
            $e    = new \Exception($text);
            $this->_messageManager->addException($e, $text);
        }
    }


    /**
     * @param \Amasty\Orderexport\Model\Profiles $profile
     * @param string                             $filePath
     */
    private function uploadByFtp($profile, $filePath)
    {
        $arrHost = explode(':', $profile->getFtpHost());
        $ftpHost = $arrHost[0];
        $ftpPort = isset($arrHost[1]) ? $arrHost[1] : false;
        if (!$ftpPort) {
            $ftpPort = 21;
        }
        $ftp = ftp_connect($ftpHost, $ftpPort, 10);
        if ($ftp) {
            $ftpLogin = ftp_login($ftp, $profile->getFtpLogin(), $profile->getFtpPassword());
            if ($ftpLogin) {
                if ($profile->getFtpIsPassive()) {
                    ftp_pasv($ftp, true);
                }
                $remotePath = $profile->getFtpPath();
                if ('/' != substr($remotePath, -1, 1) && '\\' != substr($remotePath, -1, 1)) {
                    $remotePath .= '/';
                }
                $remoteFileName = substr($filePath, strrpos($filePath, '/') + 1);
                $remotePath .= $remoteFileName;
                $upload = @ftp_put($ftp, $remotePath, $filePath, FTP_ASCII);
                if (!$upload) {
                    $text = 'Error uploading file to the FTP server.';
                    $e    = new \Exception($text);
                    $this->_messageManager->addException($e, $text);
                }
                ftp_close($ftp);


            } else {
                $text = 'Error logging in to the FTP server.';
                $e    = new \Exception($text);
                $this->_messageManager->addException($e, $text);
            }
        } else {
            $text = 'Error connecting to the FTP server.';
            $e    = new \Exception($text);
            $this->_messageManager->addException($e, $text);
        }
    }


}
