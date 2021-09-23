<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

interface ImageInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const IMAGE_ID = 'image_id';
    const TITLE = 'title';
    const STATUS = 'status';
    const BAKING_INFO = 'baking_info';
    const IMAGE_PATH = 'image_path';
    const IS_USER_UPLOAD = 'user_upload';
    /**#@-*/

    /**
     * @return int
     */
    public function getImageId(): int;

    /**
     * @param int $imageId
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setImageId(int $imageId): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setTitle(string $title): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setStatus(int $status): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string|null
     */
    public function getImagePath();

    /**
     * @param string|null $imagePath
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setImagePath($imagePath): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface[]
     */
    public function getBakingInfo(): array;

    /**
     * @param \Amasty\GiftCard\Api\Data\ImageBakingInfoInterface[] $backingInfo
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setBakingInfo(array $backingInfo): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return bool
     */
    public function isUserUpload(): bool;

    /**
     * @param bool $flag
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setIsUserUpload(bool $flag): \Amasty\GiftCard\Api\Data\ImageInterface;
}
