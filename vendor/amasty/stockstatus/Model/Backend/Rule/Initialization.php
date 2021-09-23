<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Backend\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\Rule;
use Amasty\Stockstatus\Model\Source\Status;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Initialization
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var array
     */
    private $processors;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        array $processors = []
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->processors = $processors;
    }

    /**
     * @param int $ruleId
     * @param array $inputRuleData
     * @return RuleInterface
     * @throws InputException
     */
    public function execute(int $ruleId, array $inputRuleData): RuleInterface
    {
        $ruleData = $this->retrieveRuleData($inputRuleData);

        try {
            /** @var RuleInterface|Rule $rule */
            $rule = $this->ruleRepository->getById($ruleId, true);
        } catch (NoSuchEntityException $e) {
            $rule = $this->ruleRepository->getNew();
        }

        $rule->addData($ruleData);

        foreach ($this->processors as $processor) {
            $processor->execute($rule, $inputRuleData);
        }

        return $rule;
    }

    /**
     * @param array $inputData
     * @return array
     * @throws InputException
     */
    private function retrieveRuleData(array $inputData): array
    {
        $this->validateExisting($inputData, RuleInterface::NAME);
        $this->validateExisting($inputData, RuleInterface::STORES);
        $this->validateExisting($inputData, RuleInterface::CUSTOMER_GROUPS);

        $ruleData[RuleInterface::STATUS] = (int) ($inputData[RuleInterface::STATUS] ?? Status::INACTIVE);
        $ruleData[RuleInterface::PRIORITY] = (int) ($inputData[RuleInterface::PRIORITY] ?? 1);
        $ruleData[RuleInterface::STOCK_STATUS] = $inputData[RuleInterface::STOCK_STATUS] ?? null;
        $ruleData[RuleInterface::STOCK_STATUS] = $ruleData[RuleInterface::STOCK_STATUS] ?: null;
        $ruleData[RuleInterface::CUSTOMER_GROUPS] = implode(
            ',',
            ($inputData[RuleInterface::CUSTOMER_GROUPS] ?? [])
        );
        $ruleData[RuleInterface::STORES] = implode(
            ',',
            ($inputData[RuleInterface::STORES] ?? [])
        );
        $ruleData[RuleInterface::NAME] = $inputData[RuleInterface::NAME];
        $ruleData[RuleInterface::ACTIVATE_QTY_RANGES] = (bool) (
            $inputData[RuleInterface::ACTIVATE_QTY_RANGES] ?? false
        );
        $ruleData[RuleInterface::ACTIVATE_MSI_QTY_RANGES] = (bool) (
            $inputData[RuleInterface::ACTIVATE_MSI_QTY_RANGES] ?? false
        );
        $ruleData[RuleInterface::CONDITIONS] = $inputData[RuleInterface::CONDITIONS] ?? [];

        return $ruleData;
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
