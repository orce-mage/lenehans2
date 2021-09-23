<?php

namespace Searchanise\SearchAutocomplete\Block\Adminhtml;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;

class Dashboard extends Template
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    public function __construct(
        Context $context,
        ApiSeHelper $apiSeHelper,
        array $data = []
    ) {
        $this->apiSeHelper = $apiSeHelper;

        parent::__construct($context, $data);
    }

    public function getApiSeHelper()
    {
        return $this->apiSeHelper;
    }

    public function getSearchaniseAdmin()
    {
        $searchaniseOptions = $this->apiSeHelper->getAddonOptions();
        $searchaniseOptions['options_link'] = $this->getUrl($this->apiSeHelper->getOptionsLink());
        $searchaniseOptions['re_sync_link'] = $this->getUrl($this->apiSeHelper->getReSyncLink());
        $searchaniseOptions['connect_link'] = $this->getUrl($this->apiSeHelper->getConnectLink());

        $SearchaniseAdmin = [
            'host' => $this->apiSeHelper->getServiceUrl(false),
            'PrivateKey' => $searchaniseOptions['parent_private_key'],
            'OptionsLink' => $searchaniseOptions['options_link'],
            'ReSyncLink' => $searchaniseOptions['re_sync_link'],
            'LastRequest' => $searchaniseOptions['last_request'],
            'LastResync' => $searchaniseOptions['last_resync'],
            'ConnectLink' => $searchaniseOptions['connect_link'],
            'ShowResultsControlPanel' => true,
          
            'AddonStatus' => $searchaniseOptions['addon_status'],
            'AddonVersion' => $searchaniseOptions['addon_version'],
            'Platform' => ApiSeHelper::PLATFORM_NAME,
            'PlatformEdition' => $searchaniseOptions['core_edition'],
            'PlatformVersion' => $searchaniseOptions['core_version'],
    
            'Engines' => [],
        ];
    
        if (!empty($searchaniseOptions['parent_private_key'])) {
            $stores = $this->apiSeHelper->getStores();
    
            if (!empty($stores)) {
                foreach ($stores as $keyStore => $store) {
                    $priceFormat = $this->apiSeHelper->getPriceFormat($store);
                    $privateKey = $searchaniseOptions['private_key'][$store->getId()];
                    $exportStatus = empty($searchaniseOptions['export_status'][$store->getId()])
                        ? 'none'
                        : $searchaniseOptions['export_status'][$store->getId()];
                    $priceFormat['after'] = $priceFormat['after'] ? 'true' : 'false';
    
                    $SearchaniseAdmin['Engines'][] = [
                        'Name'         => $store->getName(),
                        'PrivateKey'   => $privateKey,
                        'LangCode'     => $store->getCode(),
                        'ExportStatus' => $exportStatus,
                        'PriceFormat'  => [
                            'decimals_separator'  => $priceFormat['decimals_separator'],
                            'thousands_separator' => $priceFormat['thousands_separator'],
                            'symbol'              => $priceFormat['symbol'],
                            'decimals'            => $priceFormat['decimals'],
                            'rate'                => $priceFormat['rate'],
                            'after'               => $priceFormat['after'],
                        ],
                    ];
                }
            }
        }
    
        return $SearchaniseAdmin;
    }
}
