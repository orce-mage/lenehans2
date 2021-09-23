<?php

namespace Searchanise\SearchAutocomplete\Block;

use Magento\Framework\View\Element\Template;

class Resultwidget extends Template
{
    /**
     * Prepare layout
     *
     * @return Searchanise_SearchAutocomplete_Block_Searchresult
     */
    protected function _prepareLayout()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbs) {
            $title = __('Search results');

            $breadcrumbs->addCrumb(
                'home',
                [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'searchanise',
                [
                    'label' => $title,
                    'title' => $title
                    ]
            );
        }

        return parent::_prepareLayout();
    }
}
