<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Modifier;

use Magento\Eav\Model\AttributeRepository;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Factor\AttributeFactor;

class AttributeModifier implements ModifierInterface
{
    use MappingTrait;

    private $repository;

    public function __construct(
        AttributeRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyData(array $data)
    {
        $code = isset($data[RankingFactorInterface::CONFIG][AttributeFactor::ATTRIBUTE])
            ? $data[RankingFactorInterface::CONFIG][AttributeFactor::ATTRIBUTE]
            : false;

        if (!$code) {
            $data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING] = [];

            return $data;
        }

        $attribute = $this->repository->get('catalog_product', $code);

        $mapping = isset($data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING])
            ? $data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING]
            : [];

        $options = [];

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        foreach ($attribute->getOptions() as $option) {
            $label = trim((string) $option->getLabel());
            if (!$label) {
                continue;
            }

            $options[] = [
                'label' => $label,
                'value' => $option->getValue(),
            ];
        }

        $mapping = $this->sync($options, $mapping);

        $data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING] = $mapping;

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
