<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Return correct URL for ajax request
     *
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $ajaxUrl = $this->_urlBuilder->getUrl('amlocator/index/ajax');
        if ($query = $this->getRequest()->getParam('query')) {
            $params['query'] = $query;
        }

        return $ajaxUrl . '?' . http_build_query($params);
    }
}
