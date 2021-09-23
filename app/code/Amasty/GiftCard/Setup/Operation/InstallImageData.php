<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\Base\Helper\Deploy as DeployHelper;
use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Api\ImageRepositoryInterface;
use Amasty\GiftCard\Model\OptionSource\ImageStatus;
use Magento\Framework\Component\ComponentRegistrar;

class InstallImageData
{
    const DEPLOY_DIR = 'pub';

    private $imageData = [
        [
            ImageInterface::TITLE => 'Gift Card 1',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '340',
            ImageInterface::IMAGE_PATH => '5a5995066225a_gift-card-1.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Gift Card 2',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '340',
            ImageInterface::IMAGE_PATH => '5a5995066225a_gift-card-2.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Gift Card Price 10$',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '338',
            ImageBakingInfoInterface::POS_Y => '392',
            ImageInterface::IMAGE_PATH => '5a5995066225a_gift-card-price-10.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Gift Card Price 25$',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '338',
            ImageBakingInfoInterface::POS_Y => '392',
            ImageInterface::IMAGE_PATH => '5a5995066225a_gift-card-price-25.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Gift Card Price 50$',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '338',
            ImageBakingInfoInterface::POS_Y => '392',
            ImageInterface::IMAGE_PATH => '5a5995066225a_gift-card-price-50.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Gift Card Price 100$',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '338',
            ImageBakingInfoInterface::POS_Y => '392',
            ImageInterface::IMAGE_PATH => '5a5995066225a_gift-card-price-100.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Birthday Gift Card 1',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '190',
            ImageBakingInfoInterface::POS_Y => '373',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-birthday-gift-card-1.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Birthday Gift Card 2',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '190',
            ImageBakingInfoInterface::POS_Y => '373',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-birthday-gift-card-2.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Birthday Gift Card 3',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '48',
            ImageBakingInfoInterface::POS_Y => '62',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-birthday-gift-card-3.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Birthday Gift Card 4',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '48',
            ImageBakingInfoInterface::POS_Y => '62',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-birthday-gift-card-4.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Birthday Gift Card 5',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '365',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-birthday-gift-card-5.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Birthday Gift Card 6',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '365',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-birthday-gift-card-6.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Easter Gift Card 1',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '50',
            ImageBakingInfoInterface::POS_Y => '338',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-easter-gift-card-1.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Easter Gift Card 2',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '50',
            ImageBakingInfoInterface::POS_Y => '338',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-easter-gift-card-2.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Easter Gift Card 3',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '373',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-easter-gift-card-3.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Easter Gift Card 4',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '373',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-easter-gift-card-4.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Thanksgiving Gift Card 1',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '323',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-thanksgiving-gift-card-1.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Thanksgiving Gift Card 2',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '323',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-thanksgiving-gift-card-2.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Thanksgiving Gift Card 3',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '187',
            ImageBakingInfoInterface::POS_Y => '373',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-thanksgiving-gift-card-3.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Thanksgiving Gift Card 4',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '187',
            ImageBakingInfoInterface::POS_Y => '373',
            ImageInterface::IMAGE_PATH => '5a5995066225a_happy-thanksgiving-gift-card-4.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Xmas Gift Card 1',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '365',
            ImageInterface::IMAGE_PATH => '5a5995066225a_xmas-gift-card-1.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Xmas Gift Card 2',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '365',
            ImageInterface::IMAGE_PATH => '5a5995066225a_xmas-gift-card-2.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Xmas Gift Card 3',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '388',
            ImageInterface::IMAGE_PATH => '5a5995066225a_xmas-gift-card-3.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Xmas Gift Card 4',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '290',
            ImageBakingInfoInterface::POS_Y => '388',
            ImageInterface::IMAGE_PATH => '5a5995066225a_xmas-gift-card-4.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Xmas And Happy New Year Gift Card 1',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '101',
            ImageBakingInfoInterface::POS_Y => '371',
            ImageInterface::IMAGE_PATH => '5a5995066225a_xmas-new-year-gift-card-1.png',
            ImageInterface::IS_USER_UPLOAD => false
        ],
        [
            ImageInterface::TITLE => 'Happy Xmas And Happy New Year Gift Card 2',
            ImageInterface::STATUS => ImageStatus::ENABLED,
            ImageBakingInfoInterface::POS_X => '101',
            ImageBakingInfoInterface::POS_Y => '371',
            ImageInterface::IMAGE_PATH => '5a5995066225a_xmas-new-year-gift-card-2.png',
            ImageInterface::IS_USER_UPLOAD => false
        ]
    ];

    /**
     * @var DeployHelper
     */
    private $deployHelper;

    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    public function __construct(
        DeployHelper $deployHelper,
        ComponentRegistrar $componentRegistrar,
        ImageRepositoryInterface $imageRepository
    ) {
        $this->deployHelper = $deployHelper;
        $this->imageRepository = $imageRepository;
        $this->componentRegistrar = $componentRegistrar;
    }

    public function addImageTemplates($isUpdate = false)
    {
        $this->deployHelper->deployFolder(
            $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                'Amasty_GiftCard'
            ) . DIRECTORY_SEPARATOR . self::DEPLOY_DIR
        );
        $imageTitlePostfix = $isUpdate ? ' (New)' : '';

        foreach ($this->imageData as $imageData) {
            $bakingInfoModel = $this->imageRepository->getEmptyImageBakingInfoModel()
                ->setPosX((int)$imageData[ImageBakingInfoInterface::POS_X])
                ->setPosY((int)$imageData[ImageBakingInfoInterface::POS_Y])
                ->setIsEnabled(true)
                ->setName('code');
            $model = $this->imageRepository->getEmptyImageModel()
                ->setTitle($imageData[ImageInterface::TITLE] . $imageTitlePostfix)
                ->setStatus($imageData[ImageInterface::STATUS])
                ->setImagePath($imageData[ImageInterface::IMAGE_PATH])
                ->setIsUserUpload($imageData[ImageInterface::IS_USER_UPLOAD])
                ->setBakingInfo([$bakingInfoModel]);
            try {
                $this->imageRepository->save($model);
            } catch (\Exception $e) {
                null; //do nothing
            }
        }
    }
}
