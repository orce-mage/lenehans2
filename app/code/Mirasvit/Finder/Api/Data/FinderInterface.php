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

interface FinderInterface
{
    const TABLE_NAME = 'mst_finder';

    const ID                = 'finder_id';
    const NAME              = 'name';
    const IS_ACTIVE         = 'is_active';
    const DESTINATION_URL   = 'destination_url';
    const BLOCK_TEMPLATE    = 'block_template';
    const BLOCK_TITLE       = 'block_title';
    const BLOCK_DESCRIPTION = 'block_description';

    public function getId(): ?int;

    public function getName(): string;

    public function setName(string $value): FinderInterface;

    public function isActive(): bool;

    public function setIsActive(bool $value): FinderInterface;

    public function getDestinationUrl(): string;

    public function setDestinationUrl(string $value): FinderInterface;

    public function getBlockTemplate(): string;

    public function setBlockTemplate(string $value): FinderInterface;

    public function getBlockTitle(): string;

    public function setBlockTitle(string $value): FinderInterface;

    public function getBlockDescription(): string;

    public function setBlockDescription(string $value): FinderInterface;
}
