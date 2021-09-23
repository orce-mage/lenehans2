<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Data;

interface IconInterface
{
    const MAIN_TABLE = 'amasty_stockstatus_icon';

    const ID = 'id';
    const OPTION_ID = 'option_id';
    const STORE_ID = 'store_id';
    const PATH = 'path';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOptionId(): int;

    /**
     * @param int $optionId
     * @return void
     */
    public function setOptionId(int $optionId): void;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void;

    /**
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void;
}
