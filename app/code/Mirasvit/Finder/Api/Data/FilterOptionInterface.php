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

interface FilterOptionInterface
{
    const TABLE_NAME = 'mst_finder_filter_option';

    const ID         = 'option_id';
    const FINDER_ID  = FinderInterface::ID;
    const FILTER_ID  = FilterInterface::ID;
    const NAME       = 'name';
    const URL_KEY    = 'url_key';
    const IMAGE_PATH = 'image_path';

    public function getId(): ?int;

    public function getFinderId(): int;

    public function setFinderId(int $value): FilterOptionInterface;

    public function getFilterId(): int;

    public function setFilterId(int $value): FilterOptionInterface;

    public function getName(): string;

    public function setName(string $value): FilterOptionInterface;

    public function getUrlKey(): string;

    public function setUrlKey(string $value): FilterOptionInterface;

    public function getImagePath(): string;

    public function setImagePath(string $value): FilterOptionInterface;
}
