<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;

class ConfigProvider
{
    private $scopeConfig;

    private $request;

    private $state;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface     $request,
        State                $state
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request     = $request;
        $this->state       = $state;
    }

    public function isDeveloperMode(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_sorting/general/dev_mode');
    }

    public function isDebug(): bool
    {
        return $this->isDeveloperMode() && $this->request->getParam('debug') === 'sorting';
    }

    public function isElasticSearch(): bool
    {
        $engine = $this->scopeConfig->getValue('catalog/search/engine');

        return in_array($engine, ['elasticsearch7', 'elasticsearch6', 'elasticsearch', 'elasticsearch5', 'elastic', 'amasty_elastic']);
    }

    public function isApplicable(): bool
    {
        if (php_sapi_name() == 'cli') {
            return false;
        }

        $areaCode = $this->state->getAreaCode();

        return $areaCode == 'frontend'
            || $areaCode == 'webapi_rest'
            || ($areaCode == 'adminhtml' && $this->request->getParam('namespace') === 'sorting_preview');
    }

    /**
     * true - default
     * false - don't apply sorting for custom blocks
     */
    public function isApplySortingForCustomBlocks(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_sorting/general/apply_if_empty');
    }
}
