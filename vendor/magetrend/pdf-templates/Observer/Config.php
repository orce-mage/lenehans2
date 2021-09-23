<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Observer;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magetrend\PdfTemplates\Block\Adminhtml\Config\System\Config\Info;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface as Message;

/**
 * Config observer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Config implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    public $resourceConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    public $cacheManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Message
     */
    public $message;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Message $message
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Message $message
    ) {
        $this->curl = $curl;
        $this->resourceConfig = $resourceConfig;
        $this->cacheManager = $cache;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->message = $message;
    }

    /**
     * Execute observer
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $response = $this->sendRequest();
        if (!$response) {
            return;
        }

        if (isset($response['data']['content'])) {
            $this->resourceConfig->saveConfig(
                Info::CONFIG_NAMESPACE.base64_decode('L2xpY2Vuc2UvaW5mbw=='),
                $response['data']['content'],
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }

        if (isset($response['key'])
            && isset($response['key']['status'])
            && $response['key']['status'] == 1
        ) {
            $this->cacheManager->clean(['config']);
            return;
        }

        $this->resourceConfig->saveConfig(Info::XML_PATH_GENERAL, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->message->addError(base64_decode('VGhlIGxpY2Vuc2Uga2V5IGlzIG5vdCBpbnN0YWxsZWQgb3IgaW52YWxpZC4gSW4gb3JkZXIgdG8gdXNlIGV4dGVuc2lvbiwgeW91IG5lZWQgdG8gb2J0YWluIGFuZCBpbnN0YWxsIGEgbmV3IHZhbGlkIGxpY2Vuc2Uga2V5Lg=='));
        $this->cacheManager->clean(['config']);
    }

    /**
     * Sending Request
     * @return bool|array
     */
    private function sendRequest()
    {
        try {
            $this->curl->setOption(CURLOPT_CONNECTTIMEOUT, 1);
            $this->curl->setOption(CURLOPT_TIMEOUT, 3);
            //@codingStandardsIgnoreStart
            $this->curl->post(
                base64_decode('aHR0cDovL3d3dy5tYWdldHJlbmQuY29tL210bGljZW5zZS9hcGkzLw==').'extension/',
                [
                    'key' => $this->getKey(),
                    'url' => $this->getUrlArray(),
                    'module' => Info::MODULE_NAMESPACE
                ]
            );

            $responseBody = json_decode($this->curl->getBody(), true);
            if (!isset($responseBody['status']) || $responseBody['status'] != 'OK') {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $responseBody;
    }

    /**
     * Get all urls of stores
     * @return array
     */
    public function getUrlArray()
    {
        $stores = $this->storeManager->getStores();
        $urlArray = [];
        if (!empty($stores)) {
            foreach ($stores as $store) {
                $urlArray[] = $this->scopeConfig->getValue(
                    'web/unsecure/base_url',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getCode()
                );
            }
        }
        return $urlArray;
    }

    /**
     * Returns key
     * @return mixed
     */
    public function getKey()
    {
        return $this->scopeConfig->getValue(
            Info::CONFIG_NAMESPACE.base64_decode('L2xpY2Vuc2Uva2V5'),
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }
}
