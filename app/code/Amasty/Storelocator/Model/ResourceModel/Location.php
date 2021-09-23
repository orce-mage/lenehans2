<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel;

use Amasty\Storelocator\Model\ImageProcessor;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Select;
use Amasty\Storelocator\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Amasty\Storelocator\Model\GalleryFactory;
use Magento\Store\Model\StoreManagerInterface;

class Location extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'amasty_amlocator_location';

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Magento\Directory\Model\Region
     */
    private $region;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Amasty\Storelocator\Model\ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var GalleryFactory
     */
    private $galleryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Directory\Model\Region $region,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Model\ImageProcessor $imageProcessor,
        GalleryCollection $galleryCollection,
        GalleryFactory $galleryFactory,
        Gallery $galleryResource,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->serializer = $serializer;
        $this->region = $region;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->imageProcessor = $imageProcessor;
        $this->galleryCollection = $galleryCollection;
        $this->galleryFactory = $galleryFactory;
        $this->galleryResource = $galleryResource;
        $this->storeManager = $storeManager;
    }

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    /**
     * Perform actions before object save
     * @param AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $scheduleId = (int)$object->getDataByKey('schedule');

        if (!$scheduleId) {
            $object->unsetData('schedule');
        }

        if (($object->getOrigData('marker_img') && $object->getOrigData('marker_img') != $object->getMarkerImg())) {
            $this->imageProcessor->deleteImage($object->getOrigData('marker_img'));
            $object->setMarkerImg($object->getMarkerImg() ? $object->getMarkerImg() : '');
        }

        return $this;
    }

    protected function _beforeDelete(AbstractModel $object)
    {
        //remove location images
        $allImages = $this->galleryCollection->getImagesByLocation($object->getId());

        foreach ($allImages as $image) {
            $this->galleryResource->delete($image);
        }

        //remove location marker
        if ($markerImg = $object->getMarkerImg()) {
            $this->imageProcessor->setBasePaths(
                ImageProcessor::MARKER_IMAGE_TYPE,
                $object->getId(),
                $object->isObjectNew()
            );
            $this->imageProcessor->deleteImage($markerImg);
        }
    }

    protected function _afterSave(AbstractModel $object)
    {
        $data = $object->getData();
        if (isset($data['store_attribute']) && !empty($data['store_attribute'])) {
            $insertData = [];
            $storeId = (int)$object->getId();

            foreach ($data['store_attribute'] as $attributeId => $values) {
                $value = $values;
                if (is_array($values)) {
                    $value = implode(',', $values);
                }
                $insertData[] = [
                    'attribute_id' => $attributeId,
                    'store_id' => $storeId,
                    'value' => $value
                ];
            }

            $table = $this->getTable('amasty_amlocator_store_attribute');

            if (count($insertData) > 0) {
                $this->getConnection()->insertOnDuplicate($table, $insertData, ['value']);
            }
        }
        if ($object->getMarkerImg() && ($image = $object->getData('marker_img'))
            && $object->getOrigData('marker_img') != $object->getMarkerImg()
        ) {
            $this->imageProcessor->processImage(
                $object->getMarkerImg(),
                ImageProcessor::MARKER_IMAGE_TYPE,
                $object->getId(),
                $object->isObjectNew()
            );
        }

        if (!($object->getData('inlineEdit') || $object->getData('massAction'))) {
            $this->saveGallery($object->getData(), $object->isObjectNew());
        }

        $this->_isPkAutoIncrement = true;
    }

    private function saveGallery($data, $isObjectNew = false)
    {
        $locationId = $data['id'];
        $allImages = $this->galleryCollection->getImagesByLocation($locationId);
        $baseImgName = isset($data['base_img']) ? $data['base_img'] : '';

        if (!isset($data['gallery_image'])) {
            foreach ($allImages as $image) {
                $this->galleryResource->delete($image);
            }
            return;
        }
        $galleryImages = $data['gallery_image'];
        $imagesOfLocation = [];
        $isImport = false;

        foreach ($allImages as $image) {
            $imagesOfLocation[$image->getData('image_name')] = $image;
        }

        foreach ($galleryImages as $galleryImage) {
            if (array_key_exists($galleryImage['name'], $imagesOfLocation)) {
                unset($imagesOfLocation[$galleryImage['name']]);
            }
            if (isset($galleryImage['tmp_name']) && isset($galleryImage['name'])) {
                $isImport = true;
                $newImage = $this->galleryFactory->create();
                $newImage->addData(
                    [
                        'location_id' => $locationId,
                        'image_name' => $galleryImage['name'],
                        'is_base' => $baseImgName === $galleryImage['name'],
                        'location_is_new' => $isObjectNew
                    ]
                );
                $this->galleryResource->save($newImage);
            }
        }

        if (!empty($galleryImages) && !$isImport) {
            foreach ($imagesOfLocation as $imageToDelete) {
                $this->galleryResource->delete($imageToDelete);
            }
        }

        $baseImg = $this->galleryCollection->getByNameAndLocation($locationId, $baseImgName);

        if (!empty($baseImg->getData())) {
            foreach ($allImages as $image) {
                if ($image->getData('is_base') == true) {
                    $image->addData(['is_base' => false]);
                    $this->galleryResource->save($image);
                }
            }
            $baseImg->addData(['is_base' => true]);
            $this->galleryResource->save($baseImg);
        }
    }

    public function setAttributesData(AbstractModel $object)
    {
        if ($object->getId()) {
            $connection = $this->getConnection();

            $select = $connection->select()
                ->from(
                    ['sa' => $this->getTable('amasty_amlocator_store_attribute')]
                )
                ->joinLeft(
                    ['attr' => $this->getTable(Attribute::TABLE_NAME)],
                    '(sa.attribute_id = attr.attribute_id)'
                )
                ->joinLeft(
                    ['attr_option' => $this->getTable(Options::TABLE_NAME)],
                    '(sa.attribute_id = attr_option.attribute_id)',
                    [
                        'options_serialized' => 'attr_option.options_serialized',
                        'value_id'           => 'attr_option.value_id'
                    ]
                )
                ->where(
                    'store_id = ?',
                    (int)$object->getId()
                )
                ->where(
                    'value <> ""'
                )
                ->where(
                    'attr.frontend_input IN (?)',
                    ['boolean', 'select', 'multiselect', 'text']
                );

            $attributes = $connection->fetchAll($select);

            $preparedAttributes = $this->prepareAttributes($attributes);

            $object->setData('attributes', $preparedAttributes);
        }

        return $object;
    }

    /**
     * @param array $attributes
     *
     * @return array $result
     */
    private function prepareAttributes($attributes)
    {
        $result = [];

        $storeId = $this->storeManager->getStore(true)->getId();

        foreach ($attributes as $key => $attribute) {
            if (!array_key_exists($attribute['attribute_code'], $result)) {
                $result[$attribute['attribute_code']] = $attribute;
                $labels = $this->serializer->unserialize($attribute['label_serialized']);
                if (!empty($labels[$storeId])) {
                    $result[$attribute['attribute_code']]['frontend_label'] = $labels[$storeId];
                }
            }
            if (isset($attribute['options_serialized']) && $attribute['options_serialized']) {
                $values = explode(',', $attribute['value']);
                if (in_array($attribute['value_id'], $values)) {
                    $options = $this->serializer->unserialize($attribute['options_serialized']);
                    $optionTitle = '';
                    if (!empty($options[$storeId])) {
                        $optionTitle = $options[$storeId];
                    } elseif (isset($options[0])) {
                        $optionTitle = $options[0];
                    }

                    $result[$attribute['attribute_code']]['option_title'][] = $optionTitle;
                }
            }
            if ($attribute['frontend_input'] == 'boolean') {
                if ((int)$attribute['value'] == 1) {
                    $result[$attribute['attribute_code']]['option_title'] = __('Yes')->getText();
                } else {
                    $result[$attribute['attribute_code']]['option_title'] = __('No')->getText();
                }
            }

            if ($attribute['frontend_input'] == 'text') {
                $result[$attribute['attribute_code']]['option_title'] = $attribute['value'];
            }

        }

        return $result;
    }

    /**
     * Set _isPkAutoIncrement for saving new location
     */
    public function setResourceFlags()
    {
        $this->_isPkAutoIncrement = false;
    }

    /**
     * @param string $urlKey
     * @param array $storeIds
     *
     * @return int
     */
    public function matchLocationUrl($urlKey, $storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `stores`)';
        }

        $where = implode(' OR ', $where);
        $select = $this->getConnection()->select()
            ->from(['locations' => $this->getMainTable()])
            ->where('locations.url_key = ?', $urlKey)
            ->where($where)
            ->reset(Select::COLUMNS)
            ->columns('locations.id');

        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select = $this->joinScheduleTable($select);

        return $select;
    }

    /**
     * Join schedule table
     *
     * @param Select $select
     *
     * @return Select $select
     */
    protected function joinScheduleTable($select)
    {
        $fromPart = $select->getPart(Select::FROM);
        if (isset($fromPart['schedule_table'])) {
            return $select;
        }
        $select->joinLeft(
            ['schedule_table' => $this->getTable(Schedule::TABLE_NAME)],
            $this->getTable(self::TABLE_NAME) . '.schedule = schedule_table.id',
            ['schedule_string' => 'schedule_table.schedule']
        );

        return $select;
    }
}
