<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Number;

use Amasty\Number\Exceptions\InvalidNumberFormat;
use Amasty\Number\Exceptions\NumberGenerationFailure;
use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\SequenceStorage;

class Generator
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AbstractFormatter[]|array
     */
    private $formatterPool;

    /**
     * @var SequenceStorage
     */
    private $sequenceStorage;

    public function __construct(
        ConfigProvider $configProvider,
        SequenceStorage $sequenceStorage,
        $formatterPool = []
    ) {
        $this->configProvider = $configProvider;
        $this->sequenceStorage = $sequenceStorage;
        $this->formatterPool = $formatterPool;
    }

    /**
     * @param string $type
     * @return string
     * @throws InvalidNumberFormat
     * @throws NumberGenerationFailure
     */
    public function getNextFormattedNumber(string $type): string
    {
        if (!$newIncrementId = $this->getNumberFormat($type)) {
            throw new InvalidNumberFormat(__('Custom number format for %1 not set.', $type));
        } else {
            foreach ($this->formatterPool as $formatter) {
                if ($formatter instanceof AbstractFormatter) {
                    $newIncrementId = $formatter->format($newIncrementId);
                }
            }

            return $newIncrementId;
        }
    }

    /**
     * @param string $type
     * @return string
     */
    private function getNumberFormat(string $type)
    {
        if ($type !== ConfigProvider::ORDER_TYPE && $this->configProvider->isFormatSameAsOrder($type)) {
            return $this->sequenceStorage->getOrder()
                ? $this->sequenceStorage->getOrder()->getIncrementId()
                : $this->configProvider->getNumberFormat($type);
        }

        return $this->configProvider->getNumberFormat($type);
    }
}
