<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Cron;

class Run extends \Wyomind\MassStockUpdate\Cron\Run
{
    public $module = "massproductimport";

    /**
     * @var \Wyomind\MassProductImport\Model\ResourceModel\Profiles\CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    public $transportBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $coreDate;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Wyomind\Framework\Helper\Module
     */
    public $framework;

    /**
     * Run constructor.
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Wyomind\Framework\Helper\Module $framework
     */
    public function __construct(
        \Wyomind\MassProductImport\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Wyomind\Framework\Helper\Module $framework
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->coreDate = $coreDate;
        $this->framework = $framework;
        $this->logger = $objectManager->create("Wyomind\MassProductImport\Logger\LoggerCron");
    }
}
