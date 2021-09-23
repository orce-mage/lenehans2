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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Ui\Job\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;

class ActionColumn extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ActionColumn constructor.
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $modalNs = 'cache_warmer_job_listing.cache_warmer_job_listing.trace_container.trace_modal';
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'trace'  => [
                        'label'    => __('Inspect Trace'),
                        'callback' => [
                            [
                                'provider' => $modalNs,
                                'target'   => 'toggleModal',
                            ],
                            [
                                'provider' => $modalNs . '.trace',
                                'target'   => 'value',
                                'params'   => $item['trace'],
                            ],
                        ],
                    ],
                    'warm'   => [
                        'label' => __('Run Job'),
                        'href'  => $this->urlBuilder->getUrl('cache_warmer/job/run', [
                            JobInterface::ID => $item[JobInterface::ID],
                        ]),
                    ],
                    'delete' => [
                        'label'   => __('Delete'),
                        'href'    => $this->urlBuilder->getUrl('cache_warmer/job/delete', [
                            JobInterface::ID => $item[JobInterface::ID],
                        ]),
                        'confirm' => [
                            'title' => __('Delete job?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
