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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Finder\Repository\FinderRepository;

class FinderSource implements OptionSourceInterface
{
    private $finderRepository;

    public function __construct(
        FinderRepository $finderRepository
    ) {
        $this->finderRepository = $finderRepository;
    }

    public function toOptionArray()
    {
        $list = [];

        foreach ($this->finderRepository->getCollection() as $finder) {
            $list[] = [
                'label' => $finder->getName(),
                'value' => $finder->getId(),
            ];
        }

        return $list;
    }
}
