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

namespace Mirasvit\Sorting\Api\Data;

interface RankingFactorInterface
{
    const TABLE_NAME = 'mst_sorting_ranking_factor';

    const ID = 'factor_id';

    const NAME              = 'name';
    const IS_ACTIVE         = 'is_active';
    const TYPE              = 'type';
    const IS_GLOBAL         = 'is_global';
    const WEIGHT            = 'weight';
    const CONFIG_SERIALIZED = 'config_serialized';
    const CONFIG            = 'config';

    public function getId(): ?int;

    public function getName(): string;

    public function setName(string $value): self;

    public function getType(): string;

    public function setType(string $value): self;

    public function isActive(): bool;

    public function setIsActive(bool $value): self;

    public function isGlobal(): bool;

    public function setIsGlobal(bool $value): self;

    public function getWeight(): int;

    public function setWeight(int $value): self;

    public function getConfig(): array;

    public function setConfig(array $value): self;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfigData(string $key, $default = false);
}
