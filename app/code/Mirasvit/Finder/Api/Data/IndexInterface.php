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

namespace Mirasvit\Finder\Api\Data;

interface IndexInterface
{
    const TABLE_NAME = 'mst_finder_index';

    const ID               = 'index_id';
    const PRODUCT_ID       = 'product_id';
    const FINDER_ID        = FinderInterface::ID;
    const FILTER_ID        = FilterInterface::ID;
    const OPTION_ID        = FilterOptionInterface::ID;
    const OPTION_PARENT_ID = 'option_parent_id';
    const PATH             = 'path';

    public function getId(): ?int;

    public function getProductId(): int;

    public function setProductId(int $value): IndexInterface;

    public function getFinderId(): int;

    public function setFinderId(int $value): IndexInterface;

    public function getFilterId(): int;

    public function setFilterId(int $value): IndexInterface;

    public function getOptionId(): int;

    public function setOptionId(int $value): IndexInterface;

    public function getOptionParentId(): int;

    public function setOptionParentId(int $value): IndexInterface;

    public function getPath(): string;

    public function setPath(string $value): IndexInterface;

}
