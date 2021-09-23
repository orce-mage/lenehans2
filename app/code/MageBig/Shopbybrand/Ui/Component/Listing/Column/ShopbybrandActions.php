<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ShopbybrandActions extends Column
{
	/** Url path */
	const BRAND_URL_PATH_EDIT = 'shopbybrand/index/edit';
	const BRAND_URL_PATH_DELETE = 'shopbybrand/index/delete';
	/** @var UrlInterface */
    protected $urlBuilder;
	 /**
     * @var string
     */
    private $editUrl;
	/**
	* @param ContextInterface $context
	* @param UiComponentFactory $uiComponentFactory
	* @param UrlInterface $urlBuilder
	* @param array $components
	* @param array $data
	* @param string $editUrl
	*/
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		array $components = [],
		array $data = [],
		$editUrl = self::BRAND_URL_PATH_EDIT
	) {
		$this->urlBuilder = $urlBuilder;
		$this->editUrl = $editUrl;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	/**
	* Prepare Data Source
	*
	* @param array $dataSource
	* @return array
	*/
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as & $item) {
				$name = $this->getData('name');
				if (isset($item['option_id'])) {
					$item[$name]['edit'] = [
						'href' => $this->urlBuilder->getUrl($this->editUrl, ['option_id' => $item['option_id']]),
						'label' => __('Edit')
					];
				}
			}
		}
		return $dataSource;
	}
}
