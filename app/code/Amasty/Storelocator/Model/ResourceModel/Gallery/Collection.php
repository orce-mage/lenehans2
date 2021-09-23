<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Gallery;

use Amasty\Storelocator\Model\Gallery;
use Amasty\Storelocator\Model\ResourceModel\Gallery as GalleryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Gallery[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CollectionFactory $factory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->factory = $factory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Gallery::class, GalleryResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function getImagesByLocation($locationId)
    {
        /** @var Collection $imagesCollection */
        $imagesCollection = $this->factory->create();

        /** @var Gallery[] $images */
        $images = $imagesCollection->addFieldToFilter('location_id', $locationId)->getItems();

        return $images;
    }

    public function getByNameAndLocation($locationId, $name)
    {
        /** @var Collection $imagesCollection */
        $imagesCollection = $this->factory->create();

        /** @var Gallery $image */
        $image = $imagesCollection
            ->addFieldToFilter('location_id', $locationId)
            ->addFieldToFilter('image_name', $name)
            ->getFirstItem();

        return $image;
    }

    /**
     * @param string $locationId
     *
     * @return \Amasty\Storelocator\Model\Gallery
     */
    public function getBaseLocationImage($locationId)
    {
        $imagesCollection = $this->factory->create();
        $imagesCollection
            ->addFieldToFilter('location_id', $locationId)
            ->addFieldToFilter('is_base', 1)
            ->addOrder('is_base');

        return $imagesCollection->getFirstItem();
    }
}
