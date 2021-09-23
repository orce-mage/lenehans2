<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\DataProvider\Form;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Storelocator\Helper\Data;
use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Magento\Framework\App\RequestInterface;

/**
 * Class LocationDataProvider
 */
class LocationDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        Data $helper,
        Serializer $serializer,
        ImageProcessor $imageProcessor,
        AttributeCollection $attributeCollection,
        GalleryCollection $galleryCollection,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->helper = $helper;
        $this->serializer = $serializer;
        $this->imageProcessor = $imageProcessor;
        $this->attributeCollection = $attributeCollection;
        $this->galleryCollection = $galleryCollection;
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        /**
         * It is need for support of several fieldsets.
         * For details @see \Magento\Ui\Component\Form::getDataSourceData
         */
        if ($data['totalRecords'] > 0) {
            $locationId = (int)$data['items'][0]['id'];
            $locationModel = $this->collection->getItemById($locationId);
            if ($stateId = (int)$locationModel->getState()) {
                $locationModel->setData('state', '');
            }
            $locationModel->setData('state_id', $stateId);

            /** @var \Amasty\Storelocator\Model\ResourceModel\Location $locationResource */
            $locationResource = $locationModel->getResource();
            $locationData = $locationResource->setAttributesData($locationModel)->getData();
            foreach ($locationData['attributes'] as $attribute) {
                $locationData['store_attribute'][$attribute['attribute_id']] = $attribute['value'];
            }

            if ($locationModel->getMarkerImg()) {
                $markerName = $locationModel->getMarkerImg();
                $locationData['marker_img'] = [
                    [
                        'name' => $locationModel->getMarkerImg(),
                        'url' => $this->imageProcessor->getImageUrl(
                            [ImageProcessor::AMLOCATOR_MEDIA_PATH, $locationData['id'], $markerName]
                        )
                    ]
                ];
            }
            $galleryImages = $this->galleryCollection->getImagesByLocation($locationId);
            if (!empty($galleryImages)) {
                $locationData['gallery_image'] = [];

                foreach ($galleryImages as $image) {
                    $imgName = $image->getData('image_name');
                    array_push(
                        $locationData['gallery_image'],
                        [
                            'name' => $imgName,
                            'url' => $this->imageProcessor->getImageUrl(
                                [ImageProcessor::AMLOCATOR_GALLERY_MEDIA_PATH, $locationData['id'], $imgName]
                            )
                        ]
                    );

                    if ($image->getData('is_base') == true) {
                        $locationData['base_img'] = $imgName;
                    }
                }
            }
            $data[$locationId] = $locationData;
        }

        return $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();

        $attributes = $this->attributeCollection->preparedAttributes(true);

        foreach ($attributes as $attributeData) {
            $this->createElement(
                $attributeData
            );
        }

        $locationId = (int)$this->request->getParam('id');
        $this->meta['map']['children']['marker_img']['arguments']['data']['config']['uploaderConfig']['url'] =
            'amasty_storelocator/file/upload/type/marker_img/id/' . $locationId;

        $this->meta['image_gallery']['children']['gallery']['arguments']['data']['config']['uploaderConfig']['url'] =
            'amasty_storelocator/file/upload/type/gallery_image/id/' . $locationId;

        return $this->meta;
    }

    /**
     * Create form element
     *
     * @param array $attributeData
     */
    private function createElement($attributeData)
    {
        $configuration = &$this->meta['store_attribute']['children']
                          [$attributeData['attribute_id']]['arguments']['data']['config'];

        $configuration['label'] = $attributeData['label'];
        $configuration['componentType'] = $attributeData['frontend_input'];
        $configuration['dataScope'] = 'store_attribute.' . $attributeData['attribute_id'];
        switch ($attributeData['frontend_input']) {
            case 'boolean':
                $configuration['componentType'] = 'select';
                break;
            case 'text':
                $configuration['componentType'] = 'field';
                $configuration['formElement'] = 'input';
                break;
        }

        $configuration['options'] = $attributeData['options'];
    }
}
