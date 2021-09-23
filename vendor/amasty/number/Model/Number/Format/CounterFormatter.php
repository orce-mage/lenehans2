<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Number\Format;

use Amasty\Number\Api\CounterRepositoryInterface;
use Amasty\Number\Api\Data\CounterInterface;
use Amasty\Number\Exceptions\NumberGenerationFailure;
use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Counter\Counter;
use Amasty\Number\Model\Counter\ResourceModel\Counter\CollectionFactory;
use Amasty\Number\Model\Number\AbstractFormatter;
use Amasty\Number\Model\SequenceStorage;
use Magento\Framework\Exception\CouldNotSaveException;

class CounterFormatter extends AbstractFormatter
{
    const PLACEHOLDER = 'counter';

    /**
     * @var DateFormatter
     */
    private $dateFormatter;

    /**
     * @var CollectionFactory
     */
    private $counterCollectionFactory;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * Reset flag to prevent recursive counter reset
     *
     * @var bool
     */
    private $isCounterAlreadyReset = false;

    public function __construct(
        DateFormatter $dateFormatter,
        ConfigProvider $configProvider,
        SequenceStorage $sequenceStorage,
        CollectionFactory $counterCollectionFactory,
        CounterRepositoryInterface $counterRepository
    ) {
        parent::__construct($configProvider, $sequenceStorage);
        $this->dateFormatter = $dateFormatter;
        $this->counterRepository = $counterRepository;
        $this->counterCollectionFactory = $counterCollectionFactory;
    }

    /**
     * @param string $template
     * @return string
     * @throws NumberGenerationFailure
     */
    public function format(string $template): string
    {
        if (strpos($template, self::PLACEHOLDER) !== false) {
            return $this->replacePlaceholder(
                $template,
                self::PLACEHOLDER,
                $this->getNextCounterValue($this->getSequence()->getEntityType())
            );
        }

        return $template;
    }

    /**
     * @param string $type
     * @return string
     * @throws NumberGenerationFailure
     */
    private function getNextCounterValue(string $type): string
    {
        $scopeTypeId = $this->getSequence()->getCounterScope($type)->getScopeTypeId();
        $scopeId = $this->getSequence()->getCounterScope($type)->getScopeId();
        $counterStep = $this->getSequence()->getModifiedCounterStep($type)
            ?? $this->configProvider->getCounterStep($type);

        try {
            /** @var Counter $counter */
            $counter = $this->getSequence()->getCounterToReset()
                ?? $this->counterRepository->getMatchingCounter($type, $scopeTypeId, $scopeId);

            if ($this->isNeedToReset($counter)) {
                $counter->setCurrentValue($this->configProvider->getStartCounterFrom($type));
            } else {
                $counter->incrementCounter($counterStep);
            }

            // Do not save counter during reset from admin
            if (!$this->getSequence()->getCounterToReset()) {
                $this->counterRepository->save($counter);
            }
        } catch (CouldNotSaveException $e) {
            throw new NumberGenerationFailure();
        }

        return $this->renderPadding($type, $counter->getCurrentValue());
    }

    /**
     * @param CounterInterface $counter
     * @return bool
     */
    private function isNeedToReset(CounterInterface $counter): bool
    {
        if (!$this->isCounterAlreadyReset) {
            $counterResetDateFormat = $this->configProvider->getCounterResetOnDateChange($counter->getEntityTypeId());
            $counterResetDate = $this->dateFormatter->formatDate($counterResetDateFormat);

            if ($counterResetDate !== $this->dateFormatter->date($counterResetDateFormat, $counter->getUpdatedAt())) {
                $this->isCounterAlreadyReset = true;

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $type
     * @param int $counterValue
     * @return string
     */
    private function renderPadding(string $type, int $counterValue): string
    {
        return sprintf('%0' . $this->configProvider->getCounterPadding($type) . 'd', $counterValue);
    }
}
