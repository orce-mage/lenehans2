<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Block;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\BaseImageLocation;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Location as LocationModel;
use Amasty\Storelocator\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocator\Api\LocationCollectionForMapProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;

/**
 * @TODO: very bad implementation, it needs refactoring
 */
class Location extends \Amasty\Storelocator\Block\Location
{
    protected $_template = 'Amasty_StorePickupWithLocator::map/center.phtml';

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    private $pager;

    /**
     * @var LocationCollectionForMapProviderInterface
     */
    private $locationCollectionForMapProvider;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Location\Collection
     */
    private $locationCollection;

    /**
     * @var \Amasty\Storelocator\Api\ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var string
     */
    private $blockPager;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        File $ioFile,
        Data $dataHelper,
        AttributeCollection $attributeCollection,
        Serializer $serializer,
        ConfigProvider $configProvider,
        ImageProcessor $imageProcessor,
        Product $productModel,
        CollectionFactory $locationCollectionFactory,
        BaseImageLocation $baseImageLocation,
        LocationProductValidatorInterface $locationProductValidator,
        \Amasty\Storelocator\Api\ReviewRepositoryInterface $reviewRepository,
        LocationCollectionForMapProviderInterface $locationCollectionForMapProvider,
        $blockPager = \Amasty\StorePickupWithLocator\Block\Pager::class,
        array $data = []
    ) {
        $this->locationCollectionForMapProvider = $locationCollectionForMapProvider;
        $this->reviewRepository = $reviewRepository;
        $this->blockPager = $blockPager;

        parent::__construct(
            $context,
            $coreRegistry,
            $jsonEncoder,
            $ioFile,
            $dataHelper,
            $attributeCollection,
            $serializer,
            $configProvider,
            $imageProcessor,
            $productModel,
            $locationCollectionFactory,
            $baseImageLocation,
            $locationProductValidator,
            $reviewRepository,
            $data
        );
    }

    /**
     * @return string
     */
    public function getLeftBlockHtml()
    {
        $html = $this->setTemplate('Amasty_StorePickupWithLocator::map/left.phtml')->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getJsonLocations()
    {
        return $this->jsonEncoder->encode($this->getLocations());
    }

    /**
     * @TODO: lots of problems with performance
     * @return \Amasty\Storelocator\Model\ResourceModel\Location\Collection
     */
    public function getLocationCollection()
    {
        if (!$this->locationCollection) {
            $pageNumber = (int)$this->getRequest()->getParam('p');
            $this->locationCollection = $this->locationCollectionForMapProvider->getCollection();
            $this->locationCollection->joinScheduleTable();
            $this->locationCollection->joinMainImage();
            $this->locationCollection->addFieldToFilter(
                ['schedule_table.schedule', 'main_table.schedule', 'main_table.schedule'],
                [
                    [ // conditions for field schedule_table.schedule
                        ['like' => '%_status":"1"%']
                    ],
                    ['eq' => 0], // condition for field main_table.schedule
                    ['null' => true], // condition for field main_table.schedule
                ]
            );
            $this->applyFilters();

            if ($this->isExistBlockPager()) {
                $this->locationCollection->setCurPage($pageNumber);
                $this->locationCollection->setPageSize($this->configProvider->getPaginationLimit());
            }

            $this->reviewRepository->loadReviewForLocations($this->locationCollection->getAllIds());

            foreach ($this->locationCollection as $location) {
                /** @var LocationModel $location */
                $location->setRating($this->getRatingHtml($location));
                $location->setTemplatesHtml();
            }
        }

        return $this->locationCollection;
    }

    /**
     * @param array $locationArray
     *
     * @return array
     */
    public function getLocations($locationArray = ['items' => []])
    {
        $pickupButtonBlock = $this->getLayout()->getBlock('pickup_here_button');
        $pickupButtonHtml =  $pickupButtonBlock ? $pickupButtonBlock->toHtml() : '';

        foreach ($this->getLocationCollection()->getLocationData() as $location) {
            $location['popup_html'] .= str_replace('idForLocation', $location['id'], $pickupButtonHtml);
            $locationArray['items'][] = $location;
        }

        $locationArray['totalRecords'] = count($locationArray['items']);
        $locationArray['block'] = $this->getLeftBlockHtml();

        if ($storeListId = $this->getAmlocatorStoreList()) {
            $locationArray['storeListId'] = $storeListId;
        }

        /** @var \Magento\Store\Model\StoreManager $store */
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;

        return $locationArray;
    }

    /**
     * @return Location|AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout() && strpos($this->getNameInLayout(), 'link') === false
            && strpos($this->getNameInLayout(), 'jsinit') === false
        ) {
            if ($title = $this->configProvider->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($title);
            }

            if ($description = $this->configProvider->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }

            if ($this->isExistBlockPager()) {
                $this->configurePager();
            }
        }

        return AbstractBlock::_prepareLayout();
    }

    /**
     * @return void
     */
    public function configurePager()
    {
        $this->getPagerHtml();

        if ($this->pager) {
            if (!$this->pager->isFirstPage()) {
                $this->addPrevNext(
                    $this->getUpdateUrl(['p' => $this->pager->getCurrentPage() - 1]),
                    ['rel' => 'prev']
                );
            } elseif ($this->pager->getCurrentPage() < $this->pager->getLastPageNum()) {
                $this->addPrevNext(
                    $this->getUpdateUrl(['p' => $this->pager->getCurrentPage() + 1]),
                    ['rel' => 'next']
                );
            }
        }
    }

    /**
     * Return Pager for locator page
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->getLayout()->getBlock('amasty.chooseOnMap.pager')) {
            $this->pager = $this->getLayout()->getBlock('amasty.chooseOnMap.pager');

            return $this->pager->toHtml();
        }

        if (!$this->pager) {
            $this->pager = $this->getLayout()->createBlock(
                $this->blockPager,
                'amasty.chooseOnMap.pager'
            );

            if ($this->pager) {
                $this->pager->setUseContainer(
                    false
                )->setShowPerPage(
                    false
                )->setShowAmounts(
                    false
                )->setFrameLength(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setJump(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame_skip',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setLimit(
                    $this->configProvider->getPaginationLimit()
                )->setCollection(
                    $this->getLocationCollection()
                );

                return $this->pager->toHtml();
            }
        }

        return '';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getUpdateUrl($params = []): string
    {
        return $this->getUrl('amstorepickup/map/update', $params);
    }

    /**
     * @return bool
     */
    public function isExistBlockPager()
    {
        return $this->blockPager ? true : false;
    }

    /**
     * @return void
     * @TODO: temporary solution for applying filters
     */
    private function applyFilters(): void
    {
        $params = $this->getRequest()->getParams();

        $attributesFromRequest = [];

        if (isset($params['attributes'])) {
            foreach ($params['attributes'] as $param) {
                if ($param['name'] === LocationInterface::CURBSIDE_ENABLED && $param['value'] !== '') {
                    $this->locationCollection->addFieldToFilter(
                        LocationInterface::CURBSIDE_ENABLED,
                        (int)$param['value']
                    );
                    continue;
                }

                if (!empty($param['value']) || $param['value'] !== '') {
                    $attributesFromRequest[(int)$param['name']][] = (int)$param['value'];
                }
            }
        }

        $this->locationCollection->applyAttributeFilters($attributesFromRequest);
    }
}
