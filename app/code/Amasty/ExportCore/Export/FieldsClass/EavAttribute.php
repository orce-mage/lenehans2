<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Export\FieldsClass;

use Amasty\ExportCore\Api\Config\Entity\Field\ActionInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ExportCore\Export\Config\Eav\Attribute\OptionsConverter;
use Amasty\ExportCore\Export\Config\EntityConfig;
use Amasty\ExportCore\Export\DataHandling\ActionConfigBuilder;
use Amasty\ExportCore\Export\Filter\FilterConfigBuilder;
use Amasty\ExportCore\Export\Filter\FilterTypeResolver;
use Amasty\ExportCore\Export\Filter\Type\Select\Filter;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Component\Form\Element\MultiSelect;

class EavAttribute extends AbstractFieldsClass
{
    /**
     * @var ActionConfigBuilder
     */
    private $actionConfigBuilder;

    /**
     * @var FilterTypeResolver
     */
    private $filterTypeResolver;

    /**
     * @var OptionsConverter
     */
    private $attributeOptionsConverter;

    /**
     * @var array
     */
    private $attributeOptionsArgs = [];

    /**
     * @var FieldInterfaceFactory
     */
    protected $fieldConfigFactory;

    /**
     * @var FilterConfigBuilder
     */
    protected $filterConfigBuilder;

    /**
     * @var string
     */
    protected $eavEntityTypeCode;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    public function __construct(
        FieldInterfaceFactory $fieldConfigFactory,
        FilterConfigBuilder $filterConfigBuilder,
        ActionConfigBuilder $actionConfigBuilder,
        FilterTypeResolver $filterTypeResolver,
        OptionsConverter $attributeOptionsConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        string $eavEntityTypeCode = ''
    ) {
        $this->fieldConfigFactory = $fieldConfigFactory;
        $this->filterConfigBuilder = $filterConfigBuilder;
        $this->actionConfigBuilder = $actionConfigBuilder;
        $this->filterTypeResolver = $filterTypeResolver;
        $this->attributeOptionsConverter = $attributeOptionsConverter;
        $this->eavEntityTypeCode = $eavEntityTypeCode;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    public function execute(FieldsConfigInterface $existingConfig, EntityConfig $entityConfig): FieldsConfigInterface
    {
        $fields = [];

        $existingFields = $this->keyByFieldName($existingConfig->getFields());
        if (isset($existingFields['store_id'])) {
            $fields['store_id'] = $existingFields['store_id'];
        }

        $attributes = $this->keyByAttributeCode($this->getEavAttributes());

        $newFieldCallback = function (\ArrayIterator $fieldDetailsIterator) {
            /** @var Attribute $attribute */
            $attribute = $fieldDetailsIterator->current();

            /** @var FieldInterface $field */
            $field = $this->fieldConfigFactory->create();
            $field->setName($attribute->getAttributeCode())
                ->setFilter($this->buildFilterConfig($attribute));

            $actions = $this->buildActionsConfig($attribute);
            if ($actions) {
                $field->setActions($actions);
            }

            return $field;
        };

        return $this->mergeFields(
            $existingConfig,
            new \ArrayIterator($attributes),
            $newFieldCallback,
            $fields
        );
    }

    /**
     * Build field filter config
     *
     * @param Attribute $attribute
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface|null
     */
    private function buildFilterConfig($attribute)
    {
        $filterType = $this->filterTypeResolver->getEavAttributeFilterType($attribute);
        $this->filterConfigBuilder->setFilterType($filterType);
        if ($filterType == Filter::TYPE_ID && $attribute->usesSource()) {
            $this->filterConfigBuilder->setMetaArguments(
                $this->getAttributeOptionsArguments($attribute)
            );
        }

        return $this->filterConfigBuilder->build();
    }

    /**
     * Build field actions config
     *
     * @param Attribute $attribute
     * @return ActionInterface[]
     */
    private function buildActionsConfig($attribute)
    {
        if ($attribute->usesSource()) {
            $action = $this->actionConfigBuilder
                ->setEavEntityTypeCode($this->eavEntityTypeCode)
                ->setIsMultiselect($attribute->getFrontendInput() === MultiSelect::NAME)
                ->setPreselected(true)
                ->build();

            return $action ? [$action] : [];
        }

        return [];
    }

    /**
     * Get attribute options config arguments
     *
     * @param Attribute $attribute
     * @return ArgumentInterface[]
     */
    private function getAttributeOptionsArguments($attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        if (!isset($this->attributeOptionsArgs[$attributeCode])) {
            $options = $attribute->getSource()
                ->getAllOptions();
            $this->attributeOptionsArgs[$attributeCode] = array_merge(
                $this->attributeOptionsConverter->toConfigArguments(
                    $options,
                    'options'
                ),
                $this->attributeOptionsConverter->getConfigArgumentDataType($attribute)
            );
        }

        return $this->attributeOptionsArgs[$attributeCode];
    }

    /**
     * Get eav attributes
     *
     * @return Attribute[]
     */
    protected function getEavAttributes()
    {
        $attributes = [];
        if ($this->eavEntityTypeCode) {
            $criteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList($this->eavEntityTypeCode, $criteria)->getItems();
        }

        return $attributes;
    }

    /**
     * Key attributes by attribute code
     *
     * @param Attribute[] $attributes
     * @return Attribute[]
     */
    protected function keyByAttributeCode(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute;
        }

        return $result;
    }
}
