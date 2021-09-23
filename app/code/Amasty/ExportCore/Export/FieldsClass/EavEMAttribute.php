<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Export\FieldsClass;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ExportCore\Export\Config\Eav\Attribute\OptionsConverter;
use Amasty\ExportCore\Export\Config\EntityConfig;
use Amasty\ExportCore\Export\DataHandling\ActionConfigBuilder;
use Amasty\ExportCore\Export\Filter\FilterConfigBuilder;
use Amasty\ExportCore\Export\Filter\FilterTypeResolver;
use Amasty\ExportCore\Export\Filter\Type\Text\Filter as TextFilter;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

class EavEMAttribute extends EavAttribute
{
    /**
     * @var EntityMetadataInterface|null
     */
    private $entityMetadata;

    public function __construct(
        FieldInterfaceFactory $fieldConfigFactory,
        FilterConfigBuilder $filterConfigBuilder,
        ActionConfigBuilder $actionConfigBuilder,
        FilterTypeResolver $filterTypeResolver,
        OptionsConverter $attributeOptionsConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool,
        array $config
    ) {
        $this->entityMetadata = $metadataPool->getMetadata($config['entityType']);

        parent::__construct(
            $fieldConfigFactory,
            $filterConfigBuilder,
            $actionConfigBuilder,
            $filterTypeResolver,
            $attributeOptionsConverter,
            $searchCriteriaBuilder,
            $attributeRepository,
            $this->entityMetadata->getEavEntityType()
        );
    }

    public function execute(FieldsConfigInterface $existingConfig, EntityConfig $entityConfig): FieldsConfigInterface
    {
        parent::execute($existingConfig, $entityConfig);

        if ($linkFieldName = $this->entityMetadata->getLinkField()) {
            $fields = [$linkFieldName => $this->buildField($linkFieldName)] + $existingConfig->getFields();
            $existingConfig->setFields($fields);
        }

        return $existingConfig;
    }

    private function buildField(string $name): FieldInterface
    {
        $this->filterConfigBuilder->setFilterType(TextFilter::TYPE_ID);
        $filter = $this->filterConfigBuilder->build();

        $fieldConfig = $this->fieldConfigFactory->create();
        $fieldConfig->setName($name)
            ->setFilter($filter);

        return $fieldConfig;
    }
}
