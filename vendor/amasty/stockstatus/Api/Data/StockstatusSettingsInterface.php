<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Data;

/**
 * @api
 */
interface StockstatusSettingsInterface
{
    const MAIN_TABLE = 'amasty_stockstatus_additional_settings';

    const ID = 'id';
    const OPTION_ID = 'option_id';
    const STORE_ID = 'store_id';
    const IMAGE_PATH = 'image_path';
    const TOOLTIP_TEXT = 'tooltip_text';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getOptionId(): ?int;

    /**
     * @param int $optionId
     */
    public function setOptionId(int $optionId): void;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int $storeId
     */
    public function setStoreId(int $storeId): void;

    /**
     * @return string|null
     */
    public function getImagePath(): ?string;

    /**
     * @param string|null $imagePath
     */
    public function setImagePath(?string $imagePath): void;

    /**
     * @return string|null
     */
    public function getTooltipText(): ?string;

    /**
     * @param string $tooltipText
     */
    public function setTooltipText(string $tooltipText): void;
}
