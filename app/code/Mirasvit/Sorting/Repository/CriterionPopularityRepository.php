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

namespace Mirasvit\Sorting\Repository;

use Magento\Variable\Model\VariableFactory;

class CriterionPopularityRepository
{
    private $variableFactory;

    public function __construct(
        VariableFactory $variableFactory
    ) {
        $this->variableFactory = $variableFactory;
    }

    public function getPopularity(string $criterionCode): int
    {
        $code = $this->getCode($criterionCode);

        $variable = $this->variableFactory->create()
            ->loadByCode($code);

        return $variable->getValue() ? (int)$variable->getValue() : 0;
    }

    public function incrementPopularity(string $criterionCode): self
    {
        $code = $this->getCode($criterionCode);

        $popularity = $this->getPopularity($criterionCode) + 1;

        $variable = $this->variableFactory->create()
            ->loadByCode($code);
        $variable->setCode($code)
            ->setPlainValue((string)$popularity)
            ->save();

        return $this;
    }

    private function getCode(string $criterionCode): string
    {
        return 'mst_sorting_' . $criterionCode;
    }
}
