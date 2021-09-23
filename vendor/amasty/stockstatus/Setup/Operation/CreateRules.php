<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Base\Model\Serializer;
use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\Backend\Rule\Initialization as RuleInitialization;
use Amasty\Stockstatus\Model\Source\CustomerGroup as CustomerGroupSource;
use Amasty\Stockstatus\Model\Source\Status;
use Amasty\Stockstatus\Model\Source\StoreOptions;
use Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Modifier\Ranges as RangesModifier;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CreateRules
{
    const NEW_RULE_ID = 0;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var RuleInitialization
     */
    private $ruleInitialization;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var CustomerGroupSource
     */
    private $customerGroupSource;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        RuleInitialization $ruleInitialization,
        RuleRepositoryInterface $ruleRepository,
        CustomerGroupSource $customerGroupSource,
        Serializer $serializer
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->ruleInitialization = $ruleInitialization;
        $this->ruleRepository = $ruleRepository;
        $this->customerGroupSource = $customerGroupSource;
        $this->serializer = $serializer;
    }

    public function execute(ModuleDataSetupInterface $setup): void
    {
        try {
            $this->attributeRepository->get(Product::ENTITY, 'custom_stock_status_qty_based');
            $qtyGroupAttribute = $this->attributeRepository->get(
                Product::ENTITY,
                'custom_stock_status_qty_rule'
            );
            $this->setup = $setup;
            $this->createRules($qtyGroupAttribute->getOptions());
        } catch (NoSuchEntityException $e) {
            null;
        }
    }

    /**
     * @param AttributeOptionInterface[] $qtyGroups
     * @return void
     */
    private function createRules(array $qtyGroups): void
    {
        $customerGroupIds = array_keys($this->customerGroupSource->toArray());

        foreach ($qtyGroups as $qtyGroup) {
            $ruleData = [
                RuleInterface::STATUS => Status::ACTIVE,
                RuleInterface::NAME => $qtyGroup->getValue()
                    ? sprintf('Rule for \'%s\' group', $qtyGroup->getLabel())
                    : 'Rule for ranges without group',
                RuleInterface::PRIORITY => $qtyGroup->getValue() ? 1 : 2,
                RuleInterface::STORES => [StoreOptions::ALL_STORE_VIEWS],
                RuleInterface::CUSTOMER_GROUPS => $customerGroupIds,
                RuleInterface::STOCK_STATUS => null,
                RuleInterface::CONDITIONS => $this->getConditionsArray($qtyGroup),
                RuleInterface::ACTIVATE_QTY_RANGES => true,
                RangesModifier::GRID_RANGES => $this->getRanges($qtyGroup->getValue())
            ];

            $rule = $this->ruleInitialization->execute(self::NEW_RULE_ID, $ruleData);
            $rule->setConditionsSerialized($this->serializer->serialize($rule->getConditions()));

            $this->ruleRepository->save($rule);
        }
    }

    private function getRanges(string $qtyGroupValue): array
    {
        $select = $this->setup->getConnection()->select()->from(
            $this->setup->getTable('amasty_stockstatus_quantityranges'),
            ['qty_from', 'qty_to', 'status_id']
        )->where(
            'rule = ?',
            $qtyGroupValue
        );

        $newRanges = [];
        $oldRanges = $this->setup->getConnection()->fetchAll($select);

        foreach ($oldRanges as $oldRange) {
            $newRanges[] = [
                RangeInterface::FROM => $oldRange['qty_from'],
                RangeInterface::TO => $oldRange['qty_to'],
                RangeInterface::STATUS_ID => $oldRange['status_id'],
            ];
        }

        return $newRanges;
    }

    private function getConditionsArray(AttributeOptionInterface $qtyGroup): array
    {
        $combinedConditions = [
            'type' => \Amasty\Stockstatus\Model\Rule\Condition\Combine::class,
            'attribute' => null,
            'operator' => null,
            'value' => 1,
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => []
        ];

        if ($qtyGroup->getValue()) {
            $combinedConditions['conditions'][] = $this->getCondition(
                'custom_stock_status_qty_rule',
                $qtyGroup->getValue()
            );
        }

        $combinedConditions['conditions'][] = $this->getCondition(
            'custom_stock_status_qty_based',
            '1'
        );

        return $combinedConditions;
    }

    private function getCondition(string $attrCode, string $value): array
    {
        return [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            'attribute' => $attrCode,
            'operator' => '==',
            'value' => $value,
            'is_value_processed' => false
        ];
    }
}
