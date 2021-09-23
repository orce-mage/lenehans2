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



namespace Mirasvit\CacheWarmer\Service;

use Magento\Framework\Pricing\Render\RendererPool as PricingRendererPool;
use Mirasvit\CacheWarmer\Service\Config\HolePunchConfig;

class PricingRendererPoolService extends PricingRendererPool
{
    /** @var \Magento\Framework\Registry */
    private $registry;

    /**
     * Internal constructor, that is called from real constructor
     * Please override this one instead of overriding real __construct constructor
     * @return void
     */
    protected function _construct()
    {
        $this->registry = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Registry::class
        );
    }


    /**
     * {@inheritdoc}
     */
    protected function findDataByPattern(array $pattern)
    {
        $data = null;
        foreach ($pattern as $key) {
            $data = $this->getData($key);
            if ($data) {
                break;
            }
        }

        if (!$data) {
            $data = $this->findDefaultDataByPattern($pattern);
        }

        return $data;
    }

    /**
     * @param array $pattern
     * @return null|string
     */
    protected function findDefaultDataByPattern($pattern)
    {
        $data = $dataArray = null;

        if ($this->registry->registry(HolePunchConfig::FIND_DATA)) {
            $dataArray = $this->registry->registry(HolePunchConfig::FIND_DATA);
        }

        if (!$dataArray) {
            return $data;
        }

        foreach ($pattern as $key) {
            $keyPrepared = explode('/', $key);
            $data        = $dataArray;
            foreach ($keyPrepared as $keyValue) {
                if (isset($data[$keyValue])) {
                    $data = $data[$keyValue];
                } else {
                    $data = null;
                    break;
                }
            }
            if ($data) {
                break;
            }
        }

        return $data;
    }
}
