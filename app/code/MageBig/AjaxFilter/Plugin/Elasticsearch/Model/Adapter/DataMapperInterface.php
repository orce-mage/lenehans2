<?php


namespace MageBig\AjaxFilter\Plugin\Elasticsearch\Model\Adapter;


interface DataMapperInterface
{
    /**
     * Prepare index data for using in search engine metadata
     *
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []): array;

    /**
     * @return bool
     */
    public function isAllowed(): bool;

    /**
     * @return string
     */
    public function getFieldName(): string;
}
