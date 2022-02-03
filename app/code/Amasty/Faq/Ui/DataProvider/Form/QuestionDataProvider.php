<?php

namespace Amasty\Faq\Ui\DataProvider\Form;

use Amasty\Faq\Api\Data\TagInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Question;
use Amasty\Faq\Model\ResourceModel\Question\Collection;
use Amasty\Faq\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Amasty\Faq\Utils\Price;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class QuestionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Question
     */
    private $question;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var Price
     */
    private $priceModifier;

    /**
     * @var TagCollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        QuestionRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        Question $question,
        TagCollectionFactory $tagCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        Price $priceModifier,
        ConfigProvider $configProvider,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->question = $question;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->priceModifier = $priceModifier;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->configProvider = $configProvider;
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
            $questionId = (int)$data['items'][0]['question_id'];
            $questionModel = $this->repository->getById($questionId);
            $questionModel->getTagTitles();
            $questionData = $questionModel->getData();
            $data[$questionId] = $questionData;
            $data[$questionId]['links']['products'] = $this->getQuestionProducts($questionId);
        }

        if ($savedData = $this->dataPersistor->get('questionData')) {
            $savedQuestionId = isset($savedData['question_id']) ? $savedData['question_id'] : null;
            if (isset($data[$savedQuestionId])) {
                $data[$savedQuestionId] = array_merge($data[$savedQuestionId], $savedData);
            } else {
                $data[$savedQuestionId] = $savedData;
            }
            $this->dataPersistor->clear('questionData');
        }

        return $data;
    }

    /**
     * @param int $questionId
     *
     * @return array|null
     */
    private function getQuestionProducts($questionId = 0)
    {
        if ($productIds = $this->question->getProductIds($questionId)) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addIdFilter($productIds)
                ->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');

            $result = [];
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            foreach ($productCollection->getItems() as $product) {
                $result[] = $this->fillData($product);
            }

            return $result;
        }

        return null;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $tags = $this->tagCollectionFactory->create()->addFieldToSelect(TagInterface::TITLE)->getData();
        $tagsArray = [];
        foreach ($tags as $tag) {
            $tagsArray[] = $tag[TagInterface::TITLE];
        }
        $meta['general']['children']['tag_titles']['arguments']['data']['config']['tags'] = $tagsArray;

        if (!$this->configProvider->isSiteMapEnabled()) {
            $meta['seo']['children']['exclude_sitemap']['arguments']['data']['config']['visible'] = false;
        }

        if (!$this->configProvider->isCanonicalUrlEnabled()) {
            $meta['seo']['children']['canonical_url']['arguments']['data']['config']['visible'] = false;
        }

        return $meta;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    private function fillData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return [
            'entity_id' => $product->getId(),
            'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
            'name' => $product->getName(),
            'status' => $product->getStatus(),
            'type_id' => $product->getTypeId(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice() ? $this->priceModifier->toDefaultCurrency($product->getPrice()) : ''
        ];
    }
}
