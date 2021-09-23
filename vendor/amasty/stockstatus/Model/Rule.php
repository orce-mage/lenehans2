<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Stockstatus\Api\Data\RuleExtensionInterface;
use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Model\Indexer\Rule\RuleProcessor;
use Amasty\Stockstatus\Model\ResourceModel\Rule as RuleResource;
use Amasty\Stockstatus\Model\Rule\ConditionFactory;
use Amasty\Stockstatus\Model\Source\StoreOptions;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Store\Model\StoreManagerInterface;

class Rule extends AbstractExtensibleModel implements RuleInterface, IdentityInterface
{
    const CACHE_TAG = 'amasty_stockstatus_rule';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_stockstatus_rule';

    /**
     * @var ConditionFactory|null
     */
    private $conditionFactory;

    /**
     * @var Serializer|null
     */
    private $serializer;

    /**
     * @var StoreManagerInterface|null
     */
    private $storeManager;

    /**
     * @var RuleProcessor|null
     */
    private $ruleProcessor;

    public function _construct()
    {
        $this->_init(RuleResource::class);
        if ($this->getData('condition_factory')) {
            $this->conditionFactory = $this->getData('condition_factory');
        }
        if ($this->hasData('amasty_serializer')) {
            $this->serializer = $this->getData('amasty_serializer');
        }
        if ($this->hasData('store_manager')) {
            $this->storeManager = $this->getData('store_manager');
        }
        if ($this->hasData('rule_processor')) {
            $this->ruleProcessor = $this->getData('rule_processor');
        }
    }

    /**
     * @return Rule
     */
    public function beforeSave()
    {
        if ($this->getConditions()
            && $this->serializer !== null
            && $this->conditionFactory !== null
        ) {
            $condition = $this->conditionFactory->create()
                ->loadPost(['conditions' => $this->getConditions()]);
            $this->setConditionsSerialized($this->serializer->serialize($condition->getConditions()->asArray()));
        }

        return parent::beforeSave();
    }

    /**
     * @return Rule
     */
    public function afterSave()
    {
        $this->getResource()->addCommitCallback([$this, 'reindex']);

        return parent::afterSave();
    }

    public function reindex(): void
    {
        if ($this->ruleProcessor !== null) {
            $this->ruleProcessor->reindexRow($this->getId());
        }
    }

    public function getId()
    {
        return $this->_getData(self::ID) !== null ? (int)$this->_getData(self::ID) : null;
    }

    public function getStatus(): int
    {
        return (int)$this->_getData(self::STATUS);
    }

    public function setStatus(int $status): void
    {
        $this->setData(RuleInterface::STATUS, $status);
    }

    public function getName(): string
    {
        return $this->_getData(RuleInterface::NAME);
    }

    public function setName(string $name): void
    {
        $this->setData(RuleInterface::NAME, $name);
    }

    public function getPriority(): int
    {
        return (int)$this->_getData(RuleInterface::PRIORITY);
    }

    public function setPriority(int $priority): void
    {
        $this->setData(RuleInterface::PRIORITY, $priority);
    }

    public function getStores(): array
    {
        $stores = explode(',', $this->_getData(RuleInterface::STORES));
        $availableStores = array_keys($this->storeManager->getStores());

        if ($this->storeManager && in_array(StoreOptions::ALL_STORE_VIEWS, $stores)) {
            $stores = $availableStores;
        } else {
            $stores = array_intersect($stores, $availableStores);
        }

        return $stores;
    }

    public function setStores($stores): void
    {
        $stores = is_array($stores) ? join(',', $stores) : $stores;
        $this->setData(RuleInterface::STORES, $stores);
    }

    public function getCustomerGroups(): array
    {
        return explode(',', $this->_getData(RuleInterface::CUSTOMER_GROUPS));
    }

    public function setCustomerGroups($customerGroups): void
    {
        $customerGroups = is_array($customerGroups) ? join(',', $customerGroups) : $customerGroups;
        $this->setData(RuleInterface::CUSTOMER_GROUPS, $customerGroups);
    }

    public function getStockStatus(): ?int
    {
        return (int) $this->_getData(RuleInterface::STOCK_STATUS);
    }

    public function setStockStatus(?int $stockStatus): void
    {
        $this->setData(RuleInterface::STOCK_STATUS, $stockStatus);
    }

    public function getConditionsSerialized(): ?string
    {
        return $this->_getData(RuleInterface::CONDITIONS_SERIALIZED);
    }

    public function setConditionsSerialized(string $conditionsSerialized): void
    {
        $this->setData(RuleInterface::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    public function isActivateQtyRanges(): bool
    {
        return (bool) $this->_getData(RuleInterface::ACTIVATE_QTY_RANGES);
    }

    public function setActivateQtyRanges(bool $activateQtyRanges): void
    {
        $this->setData(RuleInterface::ACTIVATE_QTY_RANGES, $activateQtyRanges);
    }

    public function isActivateMsiQtyRanges(): bool
    {
        return (bool) $this->_getData(RuleInterface::ACTIVATE_MSI_QTY_RANGES);
    }

    public function setActivateMsiQtyRanges(bool $activateMsiQtyRanges): void
    {
        $this->setData(RuleInterface::ACTIVATE_MSI_QTY_RANGES, $activateMsiQtyRanges);
    }

    public function getConditions(): ?array
    {
        return $this->_getData(RuleInterface::CONDITIONS);
    }

    public function setConditions(array $conditions): void
    {
        $this->setData(RuleInterface::CONDITIONS, $conditions);
    }

    private function initExtensionAttributes(): void
    {
        $extensionAttributes = $this->extensionAttributesFactory->create(Rule::class, []);
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->initExtensionAttributes();
        }

        return $this->_getExtensionAttributes();
    }

    /**
     * @param RuleExtensionInterface  $extensionAttributes
     */
    public function setExtensionAttributes(RuleExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
