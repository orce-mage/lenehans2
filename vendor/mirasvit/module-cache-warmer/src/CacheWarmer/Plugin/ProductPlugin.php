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



namespace Mirasvit\CacheWarmer\Plugin;

use Magento\Framework\App\Area;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;

class ProductPlugin
{
    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @var JobRepositoryInterface
     */
    private $jobRepository;

    /**
     * ProductPlugin constructor.
     * @param AppEmulation $appEmulation
     * @param JobRepositoryInterface $jobRepository
     */
    public function __construct(
        AppEmulation $appEmulation,
        JobRepositoryInterface $jobRepository
    ) {
        $this->appEmulation  = $appEmulation;
        $this->jobRepository = $jobRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave($subject, $product)
    {
        foreach ($product->getStoreIds() as $storeId) {
            if ($storeId == 0) {
                continue;
            }

            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, false);

            $product->setStoreId($storeId);
            $this->scheduleUrl($product->getProductUrl(), $product->getId());

            $this->appEmulation->stopEnvironmentEmulation();
        }

        return $product;
    }

    /**
     * @param string $url
     * @param int $productId
     * @return void
     */
    private function scheduleUrl($url, $productId)
    {
        if (strpos($url, 'catalog/product') !== false) {
            return;
        }

        $job = $this->jobRepository->create();
        $job->setFilter(['url' => $url, 'product_id' => $productId]);
        $this->jobRepository->save($job);
    }
}
