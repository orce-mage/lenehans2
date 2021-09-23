<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml;

use Magento\Ui\Component\MassAction\Filter;
use Amasty\Storelocator\Model\ResourceModel\Schedule\Collection;

/**
 * Class Schedule
 */
abstract class Schedule extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Storelocator::storelocator';

    /**
     * @var \Amasty\Storelocator\Model\Schedule
     */
    protected $scheduleModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionModel;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Collection
     */
    protected $scheduleCollection;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Storelocator\Model\Schedule $scheduleModel,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Base\Model\Serializer $serializer,
        Filter $filter,
        Collection $scheduleCollection
    ) {
        parent::__construct($context);
        $this->scheduleModel = $scheduleModel;
        $this->logger = $logger;
        $this->sessionModel = $context->getSession();
        $this->serializer = $serializer;
        $this->filter = $filter;
        $this->scheduleCollection = $scheduleCollection;
    }
}
