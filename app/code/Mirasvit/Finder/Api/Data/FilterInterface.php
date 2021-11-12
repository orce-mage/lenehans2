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

interface FilterInterface
{
    const TABLE_NAME = 'mst_finder_filter';

    const LINK_TYPE_CUSTOM         = 'custom';
    const LINK_TYPE_ATTRIBUTE      = 'attribute';
    const SORT_MODE_ASC_STRING     = 'asc';
    const SORT_MODE_ASC_INT        = 'asc_int';
    const SORT_MODE_DESC_STRING    = 'desc';
    const SORT_MODE_DESC_INT       = 'desc_int';
    const SORT_MODE_IMPORT         = 'by_import';
    const DISPLAY_MODE_DROPDOWN    = 'dropdown';
    const DISPLAY_MODE_LABEL       = 'label';
    const DISPLAY_MODE_IMAGE_LABEL = 'image_label';

    const ID             = 'filter_id';
    const FINDER_ID      = 'finder_id';
    const LINK_TYPE      = 'link_type';
    const ATTRIBUTE_CODE = 'attribute_code';
    const NAME           = 'name';
    const DESCRIPTION    = 'description';
    const URL_KEY        = 'url_key';
    const POSITION       = 'position';
    const IS_REQUIRED    = 'is_required';
    const IS_MULTISELECT = 'is_multiselect';
    const DISPLAY_MODE   = 'display_mode';
    const SORT_MODE      = 'sort_mode';

    public function getId(): ?int;

    public function getFinderId(): int;

    public function setFinderId(int $value): FilterInterface;

    public function getLinkType(): string;

    public function setLinkType(string $value): FilterInterface;

    public function getAttributeCode(): string;

    public function setAttributeCode(string $value): FilterInterface;

    public function getName(): string;

    public function setName(string $value): FilterInterface;

    public function getDescription(): string;

    public function setDescription(string $value): FilterInterface;

    public function getUrlKey(): string;

    public function setUrlKey(string $value): FilterInterface;

    public function getPosition(): int;

    public function setPosition(int $value): FilterInterface;

    public function isRequired(): bool;

    public function setIsRequired(bool $value): FilterInterface;

    public function isMultiselect(): bool;

    public function setIsMultiselect(bool $value): FilterInterface;

    public function getDisplayMode(): string;

    public function setDisplayMode(string $value): FilterInterface;

    public function getSortMode(): string;

    public function setSortMode(string $value): FilterInterface;
}
