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

use Magento\Catalog\Model\Product\AttributeSet\Options as AttributeSetOptions;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Factor\AttributeSetFactor;

class AttributeSetModifier implements ModifierInterface
{
    use MappingTrait;

    private $options;

    public function __construct(
        AttributeSetOptions $options
    ) {
        $this->options = $options;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $mapping = isset($data[RankingFactorInterface::CONFIG][AttributeSetFactor::MAPPING])
            ? $data[RankingFactorInterface::CONFIG][AttributeSetFactor::MAPPING]
            : [];

        $mapping = $this->sync($this->options->toOptionArray(), $mapping);

        $data[RankingFactorInterface::CONFIG][AttributeSetFactor::MAPPING] = $mapping;

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
