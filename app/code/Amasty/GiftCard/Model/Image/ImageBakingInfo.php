<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image;

use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Magento\Framework\Model\AbstractModel;

class ImageBakingInfo extends AbstractModel implements ImageBakingInfoInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\ImageBakingInfo::class);
        $this->setIdFieldName(ImageBakingInfoInterface::INFO_ID);
    }

    public function getInfoId(): int
    {
        return (int)$this->_getData(self::INFO_ID);
    }

    public function setInfoId(int $infoId): ImageBakingInfoInterface
    {
        return $this->setData(self::INFO_ID, $infoId);
    }

    public function getImageId(): int
    {
        return (int)$this->_getData(self::IMAGE_ID);
    }

    public function setImageId(int $imageId): ImageBakingInfoInterface
    {
        return $this->setData(self::IMAGE_ID, $imageId);
    }

    public function isEnabled(): bool
    {
        return (bool)$this->_getData(self::IS_ENABLED);
    }

    public function setIsEnabled(bool $isEnabled): ImageBakingInfoInterface
    {
        return $this->setData(self::IS_ENABLED, $isEnabled);
    }

    public function getName(): string
    {
        return (string)$this->_getData(self::NAME);
    }

    public function setName(string $name): ImageBakingInfoInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getPosX(): int
    {
        return (int)$this->_getData(self::POS_X);
    }

    public function setPosX(int $posX): ImageBakingInfoInterface
    {
        return $this->setData(self::POS_X, $posX);
    }

    public function getPosY(): int
    {
        return (int)$this->_getData(self::POS_Y);
    }

    public function setPosY(int $posY): ImageBakingInfoInterface
    {
        return $this->setData(self::POS_Y, $posY);
    }

    public function getTextColor(): ?string
    {
        return $this->_getData(self::TEXT_COLOR);
    }

    public function setTextColor(string $textColor): ImageBakingInfoInterface
    {
        return $this->setData(self::TEXT_COLOR, $textColor);
    }

    public function getValue(): ?string
    {
        return $this->_getData(self::VALUE);
    }

    public function setValue(?string $value): ImageBakingInfoInterface
    {
        return $this->setData(self::VALUE, $value);
    }

    public function getFontSize(): ?int
    {
        return $this->_getData(self::FONT_SIZE);
    }

    public function setFontSize(?int $size): ImageBakingInfoInterface
    {
        return $this->setData(self::FONT_SIZE, $size);
    }
}
