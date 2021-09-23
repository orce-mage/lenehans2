<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image\DataProvider;

use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Api\ImageRepositoryInterface;
use Amasty\GiftCard\Model\Image\Image;
use Amasty\GiftCard\Model\Image\ResourceModel\CollectionFactory;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        FileUpload $fileUpload,
        ImageRepositoryInterface $imageRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->fileUpload = $fileUpload;
        $this->imageRepository = $imageRepository;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->getCollection()->addFieldToSelect(ImageInterface::IMAGE_ID);
        $data = parent::getData();
        if (isset($data['items'][0])) {
            $imageId = (int)$data['items'][0][ImageInterface::IMAGE_ID];
            $image = $this->imageRepository->getById($imageId);
            $this->loadedData[$imageId] = $this->prepareImageData($image->getData());
            $bakingData = [];
            /** @var ImageBakingInfoInterface $bakingInfo */
            foreach ($image->getBakingInfo() as $bakingInfo) {
                $bakingData[$bakingInfo->getName()] = $bakingInfo->getData();
            }
            $this->loadedData[$imageId]['baking_data'] = $bakingData;
        }

        $data = $this->dataPersistor->get(Image::DATA_PERSISTOR_KEY);
        if (!empty($data)) {
            $imageId = $data[ImageInterface::IMAGE_ID] ?? null;
            $this->loadedData[$imageId] = $data;
            $this->dataPersistor->clear(Image::DATA_PERSISTOR_KEY);
        }

        return $this->loadedData;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareImageData(array $data): array
    {
        if (isset($data[ImageInterface::IMAGE_PATH])) {
            $data['image'] = [
                [
                    'name' => $data[ImageInterface::IMAGE_PATH],
                    'url' => $this->fileUpload->getImageUrl(
                        $data[ImageInterface::IMAGE_PATH]
                    )
                ]
            ];
        }
        unset($data[ImageInterface::BAKING_INFO]);

        return $data;
    }
}
