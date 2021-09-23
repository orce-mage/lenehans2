<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ActionConfigBuilder;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Amasty\ExportCore\Export\Utils\Config\ArgumentConverter;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\Element\MultiSelect;

class EavOptionValue2OptionLabel extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array|null
     */
    private $map;

    /**
     * @var array
     */
    private $attributeOptions = [];

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var ArgumentConverter
     */
    private $argumentConverter;

    public function __construct(
        $config,
        EavConfig $eavConfig,
        ArgumentConverter $argumentConverter
    ) {
        parent::__construct($config);
        $this->eavConfig = $eavConfig;
        $this->argumentConverter = $argumentConverter;
    }

    public function transform($value)
    {
        $map = $this->getMap();
        $attribute = $this->getEavAttribute();
        if ($attribute && $attribute->getFrontendInput() === MultiSelect::NAME && !empty($value)) {
            $multiSelectOptions = explode(',', $value);
            $result = [];
            foreach ($multiSelectOptions as $option) {
                if (array_key_exists($option, $map)) {
                    $result[] = $map[$option];
                }
            }

            return implode(',', $result);
        } else {
            return $map[$value] ?? $value;
        }
    }

    public function prepareArguments(FieldInterface $field, $requestData): array
    {
        $arguments = [];
        if ($eavEntityType = $requestData[ActionConfigBuilder::EAV_ENTITY_TYPE_CODE] ?? null) {
            $arguments[] = $this->argumentConverter->valueToArguments(
                (string)$eavEntityType,
                ActionConfigBuilder::EAV_ENTITY_TYPE_CODE,
                'string'
            );
        }
        $arguments[] = $this->argumentConverter->valueToArguments(
            $field->getName(),
            'field',
            'string'
        );

        return array_merge([], ...$arguments);
    }

    /**
     * Get option value to option label map
     *
     * @return array
     */
    private function getMap()
    {
        if (!$this->map) {
            $this->map = [];

            $attribute = $this->getEavAttribute();
            if (!$attribute) {
                return $this->map;
            }
            $options = $this->getAttributeOptions($attribute);
            foreach ($options as $option) {
                // Skip ' -- Please Select -- ' option
                if (strlen((string)$option['value'])) {
                    $this->map[$option['value']] = $option['label'];
                }
            }
        }

        return $this->map;
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Option Value To Option Label')->getText();
    }

    private function getAttributeOptions($attribute): array
    {
        $attributeCode = $attribute->getAttributeCode();
        if (!isset($this->attributeOptions[$attributeCode])) {
            $this->attributeOptions[$attributeCode] = $attribute->getSource()->getAllOptions();
        }

        return $this->attributeOptions[$attributeCode];
    }

    protected function getEavAttribute(): ?AbstractAttribute
    {
        $attribute = null;
        if (isset($this->config[ActionConfigBuilder::EAV_ENTITY_TYPE_CODE], $this->config['field'])) {
            try {
                $attribute = $this->eavConfig->getAttribute(
                    $this->config[ActionConfigBuilder::EAV_ENTITY_TYPE_CODE],
                    $this->config['field']
                );
            } catch (LocalizedException $e) {
                return null;
            }
        }

        return $attribute;
    }
}
