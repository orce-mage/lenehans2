<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

interface CodeInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const CODE_ID = 'code_id';
    const CODE = 'code';
    const CODE_POOL_ID = 'code_pool_id';
    const STATUS = 'status';
    /**#@-*/

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     */
    public function setCodeId(int $id): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @return int
     */
    public function getCodeId(): int;

    /**
     * @param string $code
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     */
    public function setCode(string $code): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     */
    public function setCodePoolId(int $id): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @return int
     */
    public function getCodePoolId(): int;

    /**
     * @param int $status
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     */
    public function setStatus(int $status): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @return int
     */
    public function getStatus(): int;
}
