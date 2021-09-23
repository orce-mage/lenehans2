<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus;

use Amasty\Stockstatus\Model\Backend\UpdaterAttribute;
use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Product\GetQty;
use Amasty\Stockstatus\Model\Source\StockStatus;
use Amasty\Stockstatus\Model\Stockstatus\Utils\FormatDate;
use Amasty\Stockstatus\Model\Stockstatus\Utils\GetAttributeValue;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Formatter
{
    /**
     * 1 Day is 24*60*60 = 86400sec;
     */
    const ONE_DAY = 86400;

    const DEFAULT_DATE_FORMAT = 'F d, Y';

    /**
     * constants for additional params
     */
    const SOURCE_CODE = 'source_code';

    /**
     * @var GetQty
     */
    private $getQty;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FormatDate
     */
    private $formatDate;

    /**
     * @var GetAttributeValue
     */
    private $getAttributeValue;

    public function __construct(
        GetQty $getQty,
        StockRegistryInterface $stockRegistry,
        ConfigProvider $configProvider,
        FormatDate $formatDate,
        GetAttributeValue $getAttributeValue
    ) {
        $this->getQty = $getQty;
        $this->stockRegistry = $stockRegistry;
        $this->configProvider = $configProvider;
        $this->formatDate = $formatDate;
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * Find stock status text and resolve all variables.
     *
     * @param Product $product
     * @param int $statusId
     * @param array $additionalData
     * @return string
     */
    public function execute(Product $product, int $statusId, array $additionalData = []): string
    {
        $stockStatusText = (string) $product->getResource()
            ->getAttribute(StockStatus::ATTIRUBTE_CODE)
            ->getSource()
            ->getOptionText($statusId);

        return $this->replaceCustomVariables($product, $stockStatusText, $additionalData);
    }

    private function replaceCustomVariables(Product $product, string $status, array $additionalData): string
    {
        // search for attribute entries
        preg_match_all('@\{(.+?)\}@', $status, $matches);
        if (isset($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $match) {
                $status = $this->updateStatus($status, $product, $match, $additionalData);
                $status = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $status);
                $status = htmlspecialchars_decode($status);
            }
        }

        return $status;
    }

    public function updateStatus(string $status, Product $product, string $variable, array $additionalData): string
    {
        switch ($variable) {
            case 'qty-threshold':
                $sourceCode = $additionalData[self::SOURCE_CODE] ?? null;
                $result = $this->getQty->execute($product, $sourceCode);
                // threshold used only for stock. if source detected dont apply threshold
                if ($sourceCode === null) {
                    $result -= $this->stockRegistry->getStockItem($product->getId())->getMinQty();
                }
                break;
            case 'qty':
                $result = $this->getQty->execute($product, $additionalData[self::SOURCE_CODE] ?? null);
                break;
            case 'tomorrow':
                $result = $this->getCustomDateValue(self::ONE_DAY, true);
                break;
            case 'day-after-tomorrow':
                $result = $this->getCustomDateValue(2 * self::ONE_DAY, true);
                break;
            case 'yesterday':
                $result = $this->getCustomDateValue(-self::ONE_DAY, true);
                break;
            case 'expected_date':
                $value = $product->getData(UpdaterAttribute::EXPECTED_DATE_CODE);
                $result = $this->formatDate->format($value, $this->configProvider->getExpectedDateFormat());
                if ($this->isExpectedDateHide($value)) {
                    $status = '';
                }
                break;
            default:
                $result = $this->getAttributeValue->execute($product, $variable);
        }

        if (is_numeric($result)) {
            $result = $this->cutZeroes($result);
        }

        return str_replace('{' . $variable . '}', $result, $status);
    }

    private function getCustomDateValue(int $time, bool $excludeSunday): string
    {
        if ($excludeSunday && date('w', time() + $time) == 0) {
            $time += self::ONE_DAY;
        }
        $value = date('H:i d-m-Y', time() + $time);

        return $this->formatDate->format($value);
    }

    private function isExpectedDateHide(?string $value): bool
    {
        $result = false;

        if (!$this->configProvider->isExpectedDateEnabled()
            || !$value
            || ($this->configProvider->isExpectedDateCanBeExpired()
                && $this->formatDate->compareDateWithCurrentDay($value))
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string|float|mixed $value
     * @return string
     */
    private function cutZeroes($value): string
    {
        $regexp = '@(\d+(?:[^\\.]\d+)*)\\.0+@';
        $value = preg_replace($regexp, '$1', $value);

        return $value;
    }
}
