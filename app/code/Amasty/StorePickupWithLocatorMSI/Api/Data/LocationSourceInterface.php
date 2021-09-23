<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Api\Data;

interface LocationSourceInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const LOCATION_ID = 'location_id';
    const SOURCE_CODE = 'source_code';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @param int $entityId
     *
     * @return void
     */
    public function setEntityId($entityId): void;

    /**
     * @return int
     */
    public function getLocationId(): int;

    /**
     * @param int $locationId
     *
     * @return void
     */
    public function setLocationId(int $locationId): void;

    /**
     * @return string
     */
    public function getSourceCode(): string;

    /**
     * @param string $code
     *
     * @return void
     */
    public function setSourceCode(string $code): void;
}
