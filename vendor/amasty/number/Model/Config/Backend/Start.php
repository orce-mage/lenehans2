<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Config\Backend;

use Amasty\Number\Api\CounterRepositoryInterface;
use Amasty\Number\Api\Data\CounterInterface;
use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Counter\ResetHandler;
use Amasty\Number\Model\SequenceStorage;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;

class Start extends Value
{
    const INTERNAL_SCOPE_MAPPING = [
        ScopeInterface::SCOPE_STORES => ScopeInterface::SCOPE_STORE,
        ScopeInterface::SCOPE_WEBSITES => ScopeInterface::SCOPE_WEBSITE
    ];

    /**
     * @var ResetHandler
     */
    private $resetHandler;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var SequenceStorage
     */
    private $sequenceStorage;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ResetHandler $resetHandler,
        CounterRepositoryInterface $counterRepository,
        ConfigProvider $configProvider,
        ArrayManager $arrayManager,
        SequenceStorage $sequenceStorage,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->resetHandler = $resetHandler;
        $this->counterRepository = $counterRepository;
        $this->configProvider = $configProvider;
        $this->arrayManager = $arrayManager;
        $this->sequenceStorage = $sequenceStorage;
    }

    /**
     * @return Value|void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function beforeSave()
    {
        if ($this->getValue() !== $this->getOldValue()) {
            $type = $this->getEntityType();
            $modifiedCounterStep = $this->arrayManager->get('fields/increment/value', $this->getGroups($type));
            $scopeTypeId = self::INTERNAL_SCOPE_MAPPING[$this->getScope()] ?? $this->getScope();

            if ($modifiedCounterStep && $modifiedCounterStep !== $this->configProvider->getCounterStep($type)) {
                $this->sequenceStorage->setModifiedCounterStep($type, (int)$modifiedCounterStep);
            }

            /** @var CounterInterface $counter */
            $counter = $this->counterRepository->getMatchingCounter($type, $scopeTypeId, (int)$this->getScopeId());
            $counter->setStartCounterFrom((int)$this->getValue());
            $this->resetHandler->resetCounter($counter);
        }
    }

    /**
     * @return string
     */
    private function getEntityType(): string
    {
        $result = explode('/', $this->getPath());

        return $result[1] ?? '';
    }
}
