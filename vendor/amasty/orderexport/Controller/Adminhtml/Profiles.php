<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml;

use Amasty\Orderexport\Model\ProfilesRepository;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

abstract class Profiles extends \Magento\Backend\App\Action
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var ProfilesRepository
     */
    protected $profilesRepository;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        ProfilesRepository $profilesRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_dateFilter = $dateFilter;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->filter = $filter;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->profilesRepository = $profilesRepository;
        $this->backendSession = $context->getSession() ?: $this->_objectManager->get(Session::class);
        $this->logger = $logger;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Orderexport::profiles');
    }
}
