<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Backend\Rule\Initialization;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Modifier\Ranges as RangesModifier;
use Magento\Framework\Exception\InputException;

class RetrieveData
{
    /**
     * @param array $inputData
     * @param string $dataScope
     * @return array
     * @throws InputException
     */
    public function execute(array $inputData, string $dataScope = ''): array
    {
        $rangesData = [];

        if ((!$dataScope && !empty($inputData[RangesModifier::GRID_RANGES]))
            || ($dataScope && !empty($inputData[$dataScope][RangesModifier::GRID_RANGES]))
        ) {
            $inputData = $dataScope
                ? $inputData[$dataScope][RangesModifier::GRID_RANGES]
                : $inputData[RangesModifier::GRID_RANGES];

            foreach ($inputData as $inputRangeData) {
                $this->validateExisting($inputRangeData, RangeInterface::TO);
                $this->validateExisting($inputRangeData, RangeInterface::FROM);
                $this->validateExisting($inputRangeData, RangeInterface::STATUS_ID);

                $rangeData[RangeInterface::ID] = $inputRangeData[RangeInterface::ID] ?? null;
                $rangeData[RangeInterface::ID] = $rangeData[RangeInterface::ID] ?: null;
                $rangeData[RangeInterface::TO] = (int) $inputRangeData[RangeInterface::TO];
                $rangeData[RangeInterface::FROM] = (int) $inputRangeData[RangeInterface::FROM];
                $rangeData[RangeInterface::STATUS_ID] = (int) $inputRangeData[RangeInterface::STATUS_ID];
                //MSI compatibility @see Amasty\CustomStockStatusMsi\Api\Data\MsiRangeInterface
                $rangeData['source_code'] = $inputRangeData['source_code'] ?? '';

                $rangesData[] = $rangeData;
            }
        }

        return $rangesData;
    }

    /**
     * @param array $inputData
     * @param string $key
     * @throws InputException
     */
    private function validateExisting(array $inputData, string $key): void
    {
        if (!isset($inputData[$key])) {
            throw new InputException(__('The "%1" value doesn\'t exist. Enter the value and try again.', $key));
        }
    }
}
