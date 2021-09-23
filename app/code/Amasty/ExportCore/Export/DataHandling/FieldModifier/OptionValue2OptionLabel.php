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
use Magento\Framework\Data\OptionSourceInterface;

class OptionValue2OptionLabel extends AbstractModifier implements FieldModifierInterface
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
     * @var ArgumentConverter
     */
    private $argumentConverter;

    public function __construct($config, ArgumentConverter $argumentConverter)
    {
        parent::__construct($config);
        $this->argumentConverter = $argumentConverter;
    }

    public function transform($value)
    {
        $map = $this->getMap();
        if (isset($this->config[ActionConfigBuilder::IS_MULTISELECT])
            && !empty($value)
        ) {
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
        if ($optionSource = $requestData[ActionConfigBuilder::OPTION_SOURCE] ?? null) {
            $arguments = $this->argumentConverter->valueToArguments(
                (string)$optionSource,
                ActionConfigBuilder::OPTION_SOURCE,
                'object'
            );
        }

        return $arguments;
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

            $optionSource = $this->config['optionSource'] ?? null;
            if ($optionSource instanceof OptionSourceInterface) {
                $this->config['options'] = $optionSource->toOptionArray();
            }

            if (isset($this->config['options'])
                && is_array($this->config['options'])
            ) {
                foreach ($this->config['options'] as $option) {
                    // Skip ' -- Please Select -- ' option
                    if (strlen((string)$option['value'])) {
                        $this->map[$option['value']] = $option['label'];
                    }
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
}
