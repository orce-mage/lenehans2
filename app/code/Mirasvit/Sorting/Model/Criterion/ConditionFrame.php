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

namespace Mirasvit\Sorting\Model\Criterion;

class ConditionFrame
{
    /** @var ConditionNode[]; */
    private $nodes = [];

    public function loadArray(array $data): self
    {
        foreach ($data as $nodeData) {
            $node = new ConditionNode();
            $node->loadArray($nodeData);

            $this->addNode($node);
        }

        return $this;
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this->getNodes() as $node) {
            $data[] = $node->toArray();
        }

        return $data;
    }

    /** @return ConditionNode[] */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function addNode(ConditionNode $node): self
    {
        $this->nodes[] = $node;

        return $this;
    }

    public function getDirection(): string
    {
        foreach ($this->getNodes() as $node) {
            return $node->getDirection();
        }

        return 'asc';
    }
}
