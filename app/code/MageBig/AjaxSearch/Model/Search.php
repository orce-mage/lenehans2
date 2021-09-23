<?php

namespace MageBig\AjaxSearch\Model;

use \MageBig\AjaxSearch\Helper\Data as HelperData;
use \MageBig\AjaxSearch\Model\SearchFactory;

/**
 * Search class returns needed search data
 */
class Search
{
    /**
     * @var \MageBig\AjaxSearch\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageBig\AjaxSearch\Model\SearchFactory
     */
    protected $searchFactory;

    /**
     * Search constructor.
     *
     * @param HelperData $helperData
     * @param \MageBig\AjaxSearch\Model\SearchFactory $searchFactory
     */
    public function __construct(
        HelperData $helperData,
        SearchFactory $searchFactory
    ) {
        $this->helperData    = $helperData;
        $this->searchFactory = $searchFactory;
    }

    /**
     * Retrieve suggested, product data
     *
     * @return array
     */
    public function getData()
    {
        $data               = [];
        $autocompleteFields = $this->helperData->getAutocompleteFieldsAsArray();

        foreach ($autocompleteFields as $field) {
            $data[] = $this->searchFactory->create($field)->getResponseData();
        }

        return $data;
    }
}
