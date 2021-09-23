<?php

namespace MageBig\AjaxFilter\Plugin\Elasticsearch\Model\Adapter;

class AdditionalBatchDataMapper
{
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers = [];

    public function __construct(array $dataMappers = [])
    {
        $this->dataMappers = $dataMappers;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $subject
     * @param callable $proceed
     * @param array $documentData
     * @param $storeId
     * @param array $context
     * @return array
     */
    public function aroundMap(
        $subject,
        callable $proceed,
        array $documentData,
        $storeId,
        $context = []
    ): array {
        $documentData = $proceed($documentData, $storeId, $context);
        foreach ($documentData as $productId => $document) {
            $context['document'] = $document;
            foreach ($this->dataMappers as $mapper) {
                if ($mapper instanceof DataMapperInterface
                    && $mapper->isAllowed()
                    && !isset($document[$mapper->getFieldName()])
                ) {
                    // @codingStandardsIgnoreLine
                    $document = array_merge($document, $mapper->map($productId, $document, $storeId, $context));
                }
            }
            $documentData[$productId] = $document;
        }

        return $documentData;
    }
}

