<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Plugin\Sales;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;

class Order
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Amasty\Orderexport\Model\ResourceModel\Profiles\CollectionFactory
     */
    protected $profilesCollectionFactory;

    /**
     * @var \Amasty\Orderexport\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Orderexport\Model\ResourceModel\Profiles\CollectionFactory $profilesCollectionFactory,
        \Amasty\Orderexport\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->profilesCollectionFactory = $profilesCollectionFactory;
    }

    /**
     * @param OrderRepository $subject
     * @param OrderInterface $entity
     *
     * @return OrderInterface
     */
    public function afterSave(OrderRepository $subject, OrderInterface $entity)
    {
        if (!$this->helper->getModuleConfig('general/enabled')
            || $entity->getOrigData() != null
            || $this->registry->registry('amorderexport_manual_run_triggered')
            || $this->registry->registry('amorderexport_auto_run_triggered')
        ) {
            return $entity;
        }
        $collection = $this->profilesCollectionFactory->create()
            ->addFieldToFilter('run_after_order_creation', 1);

        foreach ($collection->getItems() as $profile) {
            $profile->run(null);
        }

        return $entity;
    }
}
