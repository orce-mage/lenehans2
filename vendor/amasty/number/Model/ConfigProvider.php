<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_PREFIX = 'amnumber/';
    const XPATH_ENABLED = 'general/enabled';
    const XPATH_OFFSET = 'general/offset';
    const XPATH_SEPARATE_CONNECTION = 'general/separate_connection';
    /**#@-*/

    const PART_NUMBER_FORMAT = '/format';
    const PART_NUMBER_COUNTER = '/counter';
    const PART_NUMBER_SAME = '/same';
    const PART_NUMBER_PREFIX = '/prefix';
    const PART_NUMBER_PREFIX_REPLACE = '/replace';
    const PART_COUNTER_FROM = '/start';
    const PART_INCREMENT_STEP = '/increment';
    const PART_COUNTER_PAD = '/pad';
    const PART_COUNTER_RESET_DATE = '/reset';
    const PART_SEPARATE_WEBSITE = '/per_website';
    const PART_SEPARATE_STORE = '/per_store';

    const DEFAULT_COUNTER_STEP = 1;
    const ORDER_TYPE = 'order';
    const INVOICE_TYPE = 'invoice';
    const SHIPMENT_TYPE = 'shipment';
    const CREDITMEMO_TYPE = 'creditmemo';

    const AVAILABLE_ENTITY_TYPES = [
        self::ORDER_TYPE,
        self::INVOICE_TYPE,
        self::SHIPMENT_TYPE,
        self::CREDITMEMO_TYPE
    ];

    protected $pathPrefix = self::XPATH_PREFIX;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var App\State
     */
    private $state;

    private $storeId;

    public function __construct(
        App\State $state,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($scopeConfig);
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }

    /**
     * @return mixed
     */
    public function getTimezoneOffset()
    {
        return $this->getValue(self::XPATH_OFFSET);
    }

    /**
     * @return bool
     */
    public function isUseSeparateConnection(): bool
    {
        return $this->isSetFlag(self::XPATH_SEPARATE_CONNECTION);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getNumberFormat(string $type): string
    {
        if ($type !== self::ORDER_TYPE && $this->isFormatSameAsOrder($type)) {
            return $this->getNumberFormat(self::ORDER_TYPE);
        }

        return (string)$this->getScopedValue($type . self::PART_NUMBER_FORMAT);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isFormatSameAsOrder($type): bool
    {
        return !!$this->getScopedValue($type . self::PART_NUMBER_SAME);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getNumberPrefix($type)
    {
        return (string)$this->getScopedValue($type . self::PART_NUMBER_PREFIX);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getNumberReplacePrefix($type)
    {
        return (string)$this->getScopedValue($type . self::PART_NUMBER_PREFIX_REPLACE);
    }

    /**
     * @param string $type
     * @return int
     */
    public function getStartCounterFrom($type): int
    {
        $start = (int)$this->getScopedValue($type . self::PART_COUNTER_FROM);

        if ($start <= 0) {
            $start = self::DEFAULT_COUNTER_STEP;
        }

        return $start;
    }

    /**
     * @param string $type
     * @return int
     */
    public function getCounterStep($type): int
    {
        $step = (int)$this->getScopedValue($type . self::PART_INCREMENT_STEP);

        if ($step <= 0) {
            $step = self::DEFAULT_COUNTER_STEP;
        }

        return $step;
    }

    /**
     * @param string $type
     * @return int
     */
    public function getCounterPadding($type): int
    {
        return (int)$this->getScopedValue($type . self::PART_COUNTER_PAD);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCounterResetOnDateChange($type): string
    {
        return (string)$this->getScopedValue($type . self::PART_COUNTER_RESET_DATE);
    }

    /**
     * @param $type
     * @param string $scope
     * @param $scopeId
     * @return bool
     */
    public function isSeparateCounter($type, $scope = ScopeInterface::SCOPE_STORE, $scopeId = null): bool
    {
        switch ($scope) {
            case ScopeInterface::SCOPE_WEBSITE:
                return !!$this->getValue(
                    $type . self::PART_SEPARATE_WEBSITE,
                    $scopeId,
                    ScopeInterface::SCOPE_WEBSITE
                );
            case ScopeInterface::SCOPE_STORE:
                return !!$this->getValue(
                    $type . self::PART_SEPARATE_STORE,
                    $scopeId,
                    ScopeInterface::SCOPE_STORE
                );
            default:
                return false;
        }
    }

    /**
     * @param $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * Get scope data on admin within defined storeID via setStoreId() method.
     * Counter number config must have correct scope during Order placement or Invoice/Shipping/Memo creating on admin
     * because scope config could not be automatically resolved on admin area.
     *
     * @param $path
     * @return mixed
     */
    private function getScopedValue($path)
    {
        try {
            if (!$this->isAdminArea()) {
                return $this->getValue($path);
            }

            $storeValue = $this->getValue(
                $path,
                $this->storeManager->getStore($this->storeId)->getId(),
                ScopeInterface::SCOPE_STORE
            );
            $websiteValue = $this->getValue(
                $path,
                $this->storeManager->getStore($this->storeId)->getWebsiteId(),
                ScopeInterface::SCOPE_WEBSITE
            );
        } catch (\Throwable $e) {
            null;
        }

        return $storeValue ?? $websiteValue ?? $this->getValue($path);
    }

    /**
     * @return bool
     */
    private function isAdminArea()
    {
        try {
            return $this->state->getAreaCode() === App\Area::AREA_ADMINHTML;
        } catch (\Throwable $e) {
            null;
        }

        return false;
    }
}
