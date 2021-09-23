<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Api\Data;

interface ImageBakingInfoInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const INFO_ID = 'info_id';
    const IMAGE_ID = 'image_id';
    const IS_ENABLED = 'is_enabled';
    const NAME = 'name';
    const POS_X = 'pos_x';
    const POS_Y = 'pos_y';
    const TEXT_COLOR = 'text_color';
    const VALUE = 'value';
    const FONT_SIZE = 'font_size';
    /**#@-*/

    /**
     * @return int
     */
    public function getInfoId(): int;

    /**
     * @param int $infoId
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface
     */
    public function setInfoId(int $infoId): ImageBakingInfoInterface;

    /**
     * @return int
     */
    public function getImageId(): int;

    /**
     * @param int $imageId
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface
     */
    public function setImageId(int $imageId): ImageBakingInfoInterface;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $isEnabled
     * @return ImageBakingInfoInterface
     */
    public function setIsEnabled(bool $isEnabled): ImageBakingInfoInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface
     */
    public function setName(string $name): ImageBakingInfoInterface;

    /**
     * @return int
     */
    public function getPosX(): int;

    /**
     * @param int $posX
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface
     */
    public function setPosX(int $posX): ImageBakingInfoInterface;

    /**
     * @return int
     */
    public function getPosY(): int;

    /**
     * @param int $posY
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface
     */
    public function setPosY(int $posY): ImageBakingInfoInterface;

    /**
     * @return string
     */
    public function getTextColor(): ?string;

    /**
     * @param string $textColor
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface
     */
    public function setTextColor(string $textColor): ImageBakingInfoInterface;

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     * @return ImageBakingInfoInterface
     */
    public function setValue(?string $value): ImageBakingInfoInterface;

    /**
     * @return int|null
     */
    public function getFontSize(): ?int;

    /**
     * @param int|null $size
     * @return ImageBakingInfoInterface
     */
    public function setFontSize(?int $size): ImageBakingInfoInterface;
}
