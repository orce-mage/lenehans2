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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Block;

use Magento\Framework\View\Element\Template;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Model\ConfigProvider;

class Js extends Template
{
    protected $_template = 'Mirasvit_Finder::js.phtml';

    private   $finder;

    private   $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;

        parent::__construct($context, $data);
    }

    public function getFinder(): FinderInterface
    {
        return $this->finder;
    }

    public function setFinder(FinderInterface $finder): Js
    {
        $this->finder = $finder;

        return $this;
    }

    public function getResetUrl(): string
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => [
            ConfigProvider::REQUEST_VAR => null,
        ]]);
    }

    public function getFindUrl(): string
    {
        if ($this->configProvider->isFriendlyUrl()) {
            return $this->getRedirectUrl();
        }

        /** @var \Magento\Framework\App\Request\Http $request */
        $request        = $this->getRequest();
        $fullActionName = $request->getFullActionName();

        if ($this->getFinder()->getDestinationUrl()) {
            return $this->getFinder()->getDestinationUrl() . '?' . ConfigProvider::REQUEST_VAR . '=___finder___';
        }

        if (in_array($fullActionName, ['catalog_category_view', 'catalogsearch_result_index'])) {
            //page with products
            return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => [
                ConfigProvider::REQUEST_VAR => '___finder___',
            ]]);
        }

        return $this->getUrl($this->configProvider->getResultRoute(), ['_current' => true, '_query' => [
            ConfigProvider::REQUEST_VAR => '___finder___',
        ]]);
    }

    public function getRedirectUrl(): string
    {
        if ($this->getFinder()->getDestinationUrl()) {
            return $this->getUrl('mst_finder/filter/redirect', ['_query' => [
                'finder_id'                 => $this->getFinder()->getId(),
                ConfigProvider::REQUEST_VAR => '___finder___',
            ]]);
        }

        /** @var \Magento\Framework\App\Request\Http $request */
        $request        = $this->getRequest();
        $fullActionName = $request->getFullActionName();
        if (in_array($fullActionName, ['catalog_category_view', 'catalogsearch_result_index'])) {
            $categoryId = (int)$this->getRequest()->getParam('id');

            return $this->getUrl('mst_finder/filter/redirect', ['_query' => [
                'finder_id'                 => $this->getFinder()->getId(),
                'category_id'               => $categoryId,
                ConfigProvider::REQUEST_VAR => '___finder___',
            ]]);
        }

        return $this->getUrl($this->configProvider->getResultRoute(), ['_current' => true, '_query' => [
            ConfigProvider::REQUEST_VAR => '___finder___',
        ]]);
    }
}
