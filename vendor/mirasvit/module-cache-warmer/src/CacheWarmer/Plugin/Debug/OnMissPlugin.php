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



namespace Mirasvit\CacheWarmer\Plugin\Debug;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\CacheWarmer\Api\Data\PageTypeInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Mirasvit\CacheWarmer\Service\Config\HolePunchConfig;

class OnMissPlugin
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var HolePunchConfig
     */
    private $holePunchConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Context
     */
    private $context;

    /**
     * OnMissPlugin constructor.
     * @param Registry $registry
     * @param HolePunchConfig $holePunchConfig
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param Request $request
     * @param Serializer $serializer
     */
    public function __construct(
        Registry $registry,
        HolePunchConfig $holePunchConfig,
        StoreManagerInterface $storeManager,
        Context $context,
        Request $request,
        Serializer $serializer
    ) {
        $this->registry        = $registry;
        $this->holePunchConfig = $holePunchConfig;
        $this->storeManager    = $storeManager;
        $this->context         = $context;
        $this->request         = $request;
        $this->serializer = $serializer;
    }

    /**
     * Use for block excluding
     * @param \Magento\Framework\App\PageCache\Kernel $subject
     * @param ResponseInterface                       $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess($subject, ResponseInterface $response)
    {
        if ($this->request->isAjax() || $this->isAttachmentLink()) {
            return;
        }
        /** @var \Magento\Framework\App\Response\Http $response */
        $this->registerData(); //need for cms blocks excluding
        $storeId   = $this->storeManager->getStore()->getId();
        $templates = $this->holePunchConfig->getTemplates($storeId);
        if ($templates && ($product = $this->registry->registry('current_product'))) {
            $response->setHeader('m_prod', $product->getId(), true);
        } elseif ($templates && ($category = $this->registry->registry('current_category'))) {
            $response->setHeader('m_cat', $category->getId(), true);
        }

        $matches = [];
        //fix error "(InvalidArgumentException): Unable to serialize value." when json_last_error() return 5
        if (is_object($response->getHeader('Cache-Control'))
            && preg_match(
                '/public.*s-maxage=(\d+)/',
                $response->getHeader('Cache-Control')->getFieldValue(),
                $matches
            )
        ) {
            if (($response->getHttpResponseCode() == 200 || $response->getHttpResponseCode() == 404)
                && ($this->request->isGet() || $this->request->isHead())
            ) {
                $content = $response->getContent();
                if ($content && json_encode($content) === false && json_last_error() == 5) {
                    $content = mb_convert_encoding($content, "UTF-8", "auto");
                    $response->setContent($content);
                }
            }
        }
    }

    /**
     * @return void
     */
    private function registerData()
    {
        $findData = $this->registry->registry(HolePunchConfig::FIND_DATA);
        if ($findData) {
            $this->context->setValue(
                HolePunchConfig::FIND_DATA,
                $this->serializer->serialize($findData),
                $this->serializer->serialize($findData)
            );
        }
    }

    /**
     * @param \Magento\Framework\App\PageCache\Kernel $subject
     * @param \Closure                                $proceed
     * @param ResponseInterface                       $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcess($subject, \Closure $proceed, ResponseInterface $response)
    {
        if ($this->request->isAjax() || $this->isAttachmentLink()) {
            return $proceed($response);
        }
        
        /** @var \Magento\Framework\App\Response\Http $response */
        //forbid add in cache empty page
        if (is_object($response->getHeader('Cache-Control')) && $response->getBody()) {
            return $proceed($response);
        }
    }

    /**
     * @return bool
     */
    protected function isAttachmentLink()
    {
        return strpos($this->request->getOriginalPathInfo(), '/attachment/') !== false;
    }
}
