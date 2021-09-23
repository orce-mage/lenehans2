<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Setup\Operation;

use Amasty\Number\Exceptions\InvalidNumberFormat;
use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Counter\CounterRepository;
use Amasty\Number\Model\Counter\ResourceModel\Counter\CollectionFactory as CounterCollectionFactory;
use Amasty\Number\Model\Number\Validator;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\StoreManagerInterface;

class UpgradeDataTo200
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CounterRepository
     */
    private $counterRepository;

    /**
     * @var CollectionFactory
     */
    private $configCollectionFactory;

    /**
     * @var CounterCollectionFactory
     */
    private $counterCollectionFactory;

    public function __construct(
        Validator $validator,
        StringUtils $stringUtils,
        ConfigProvider $configProvider,
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        CounterRepository $counterRepository,
        CollectionFactory $configCollectionFactory,
        CounterCollectionFactory $counterCollectionFactory
    ) {
        $this->validator = $validator;
        $this->stringUtils = $stringUtils;
        $this->configProvider = $configProvider;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->counterRepository = $counterRepository;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->counterCollectionFactory = $counterCollectionFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        /** @var Value $configCounter */
        foreach ($this->getExistingCountersByField(ConfigProvider::PART_NUMBER_COUNTER) as $configCounter) {
            if ($entityType = $this->resolveEntityTypeByPath($configCounter->getPath())) {
                $newCounter = $this->counterRepository->create()
                    ->setScopeId($configCounter->getScopeId())
                    ->setScopeTypeId($configCounter->getScope())
                    ->setEntityTypeId($entityType)
                    ->setCurrentValue($configCounter->getValue());
                $this->counterRepository->save($newCounter);
            }
        }

        foreach ($this->getExistingCountersByField(ConfigProvider::PART_NUMBER_FORMAT) as $configCounter) {
            $entityType = $this->resolveEntityTypeByPath($configCounter->getPath());

            try {
                $this->validator->validatePattern($entityType, $configCounter->getValue());
            } catch (InvalidNumberFormat $e) {
                if (isset(Validator::EXAMPLE_FORMATS[$entityType])) {
                    $configCounter->setValue(Validator::EXAMPLE_FORMATS[$entityType]);
                    $this->config->saveConfig(
                        $configCounter->getPath(),
                        $configCounter->getValue(),
                        $configCounter->getScope(),
                        $configCounter->getScopeId()
                    );
                }
            }
        }
    }

    /**
     * @param string $field
     * @return array|DataObject[]
     */
    private function getExistingCountersByField(string $field = '')
    {
        $entityCounters = [];
        $configCollection = $this->configCollectionFactory->create();

        foreach (ConfigProvider::AVAILABLE_ENTITY_TYPES as $entityType) {
            $entityCounters[] = ConfigProvider::XPATH_PREFIX . $entityType . $field;
        }

        if (count($entityCounters) > 0) {
            $configCollection->addFieldToFilter('path', ['in' => $entityCounters]);

            return $configCollection->getItems();
        }

        return [];
    }

    /**
     * @param string $path
     * @return mixed|null
     */
    private function resolveEntityTypeByPath(string $path)
    {
        foreach (ConfigProvider::AVAILABLE_ENTITY_TYPES as $entityType) {
            $searchPath = ConfigProvider::XPATH_PREFIX . $entityType . '/';

            if ($this->stringUtils->strpos($path, $searchPath) !== false) {
                return $entityType;
            }
        }

        return null;
    }
}
