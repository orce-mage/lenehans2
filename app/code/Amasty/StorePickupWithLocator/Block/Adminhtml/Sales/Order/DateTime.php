<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Block\Adminhtml\Sales\Order;

use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\Order as OrderEntity;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class Quote
 */
class DateTime extends Template
{
    const DATE = 'date';
    const TIME_FROM = 'time_from';
    const TIME_TO = 'time_to';

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    /**
     * @var array
     */
    protected $storage = [];

    public function __construct(
        Template\Context $context,
        TimezoneInterface $timezone,
        ConfigProvider $configProvider,
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        TimeHandler $timeHandler,
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->timeHandler = $timeHandler;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_StorePickupWithLocator::sales/order/view/datetime.phtml');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->isExistDateData()) {
            return __('Pickup Date');
        }

        return '';
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if ($this->isExistDateData()) {
            return $this->timezone->formatDate($this->storage[self::DATE]);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTime()
    {
        if (isset($this->storage[self::TIME_FROM]) && isset($this->storage[self::TIME_TO])) {
            $timeFrom = $this->timeHandler->convertTime($this->storage[self::TIME_FROM]);
            $timeTo = $this->timeHandler->convertTime($this->storage[self::TIME_TO]);

            return $timeFrom . ' - ' . $timeTo;
        }

        return '';
    }

    /**
     * @return bool
     */
    private function isExistDateData()
    {
        if (!isset($this->storage[self::DATE])
            || !isset($this->storage[self::TIME_FROM])
            || !isset($this->storage[self::TIME_TO])
        ) {
            if ($orderId = $this->request->getParam(OrderItemInterface::ORDER_ID)) {
                /** @var OrderEntity $orderEntity */
                $orderEntity = $this->orderRepository->getByOrderId($orderId);
                if ($orderEntity->getDate()) {
                    $this->storage[self::DATE] = $orderEntity->getDate();
                    if ($orderEntity->getTimeFrom() && $orderEntity->getTimeTo()) {
                        $this->storage[self::TIME_FROM] = $orderEntity->getTimeFrom();
                        $this->storage[self::TIME_TO] = $orderEntity->getTimeTo();
                    }
                    return true;
                }
            }
            return false;
        }
        return true;
    }
}
