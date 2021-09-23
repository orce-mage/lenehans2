<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface RuleInterface extends ExtensibleDataInterface
{
    const MAIN_TABLE = 'amasty_stockstatus_rule';

    const ID = 'id';
    const STATUS = 'status';
    const NAME = 'name';
    const PRIORITY = 'priority';
    const STORES = 'stores';
    const CUSTOMER_GROUPS = 'customer_groups';
    const STOCK_STATUS = 'stock_status';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIVATE_QTY_RANGES = 'activate_qty_ranges';
    const ACTIVATE_MSI_QTY_RANGES = 'activate_msi_qty_ranges';

    /**
     * Constant for store array of rule conditions
     */
    const CONDITIONS = 'conditions';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return RuleInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     * @return void
     */
    public function setPriority(int $priority): void;

    /**
     * @return string[]
     */
    public function getStores(): array;

    /**
     * @param string|string[] $stores
     * @return void
     */
    public function setStores($stores): void;

    /**
     * @return string[]
     */
    public function getCustomerGroups(): array;

    /**
     * @param string|string[] $customerGroups
     * @return void
     */
    public function setCustomerGroups($customerGroups): void;

    /**
     * @return int|null
     */
    public function getStockStatus(): ?int;

    /**
     * @param int|null $stockStatus
     * @return void
     */
    public function setStockStatus(?int $stockStatus): void;

    /**
     * @return string|null
     */
    public function getConditionsSerialized(): ?string;

    /**
     * @param string $conditionsSerialized
     * @return void
     */
    public function setConditionsSerialized(string $conditionsSerialized): void;

    /**
     * @return bool
     */
    public function isActivateQtyRanges(): bool;

    /**
     * @param bool $activateQtyRanges
     * @return void
     */
    public function setActivateQtyRanges(bool $activateQtyRanges): void;

    /**
     * @return bool
     */
    public function isActivateMsiQtyRanges(): bool;

    /**
     * @param bool $activateMsiQtyRanges
     * @return void
     */
    public function setActivateMsiQtyRanges(bool $activateMsiQtyRanges): void;

    /**
     * @return string[]|null
     */
    public function getConditions(): ?array;

    /**
     * @param array $conditions
     * @return void
     */
    public function setConditions(array $conditions): void;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\Stockstatus\Api\Data\RuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\Stockstatus\Api\Data\RuleExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(\Amasty\Stockstatus\Api\Data\RuleExtensionInterface $extensionAttributes);
}
