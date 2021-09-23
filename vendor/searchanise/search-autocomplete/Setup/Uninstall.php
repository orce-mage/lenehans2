<?php

namespace Searchanise\SearchAutocomplete\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Searchanise\SearchAutocomplete\Helper\ApiSe
     */
    private $apiSe;

    public function __construct(\Searchanise\SearchAutocomplete\Helper\ApiSe $apiSe)
    {
        $this->apiSe = $apiSe;
    }

    // Module delete (Works only to delete module via composer)
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $stores = $this->apiSe->getStores();
        foreach ($stores as $store) {
            $this->apiSe->sendAddonStatusRequest('deleted', $store);
        }

        $setup->endSetup();
    }
}
