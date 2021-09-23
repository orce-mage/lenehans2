<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Block\Widget;

class BrandSearch extends \MageBig\Shopbybrand\Block\Widget\BrandAbstract
{
	protected $_cacheTag = 'BRAND_SEARCH';

    public function getJsonConfig() {
        $brandRoute = $this->_helper->getBrandRoute();
        $url = 'ourbrands/index/searchBrands';
        $config = [
            'MageBig_Shopbybrand/js/brands' => [
                'magebig.searchBrands' => [
                    'brandUrl'  => $this->getUrl($url)
                ]
            ]
        ];

        return $this->_serialize->serialize($config);
    }
}
