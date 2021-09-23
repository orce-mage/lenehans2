<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Form;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;

class CompositeForm implements \Amasty\ExportCore\Api\FormInterface
{
    /**
     * @var \Amasty\ExportCore\Api\FormInterface[]
     */
    private $formGroupProviders;

    public function __construct(
        array $metaProviders
    ) {
        usort($metaProviders, function ($first, $second) {
            return ($first['sortOrder'] ?? 0) <=> ($second['sortOrder'] ?? 0);
        });
        $this->setFormGroupProviders($metaProviders);
    }

    public function getFormGroupProviders(): array
    {
        return $this->formGroupProviders;
    }

    public function setFormGroupProviders(array $formGroupProviders): FormInterface
    {
        $this->formGroupProviders = $formGroupProviders;

        return $this;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [];
        foreach ($this->getFormGroupProviders() as $formGroup) {
            $result = array_merge_recursive(
                $result,
                $formGroup['metaClass']->getMeta($entityConfig, $formGroup['arguments'] ?? [])
            );
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        $result = [];
        foreach ($this->getFormGroupProviders() as $formGroup) {
            $result = array_merge_recursive($result, $formGroup['metaClass']->getData($profileConfig));
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        foreach ($this->getFormGroupProviders() as $formGroup) {
            $formGroup['metaClass']->prepareConfig($profileConfig, $request);
        }

        return $this;
    }
}
