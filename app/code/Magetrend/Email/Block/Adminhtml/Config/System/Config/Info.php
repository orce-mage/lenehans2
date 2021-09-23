<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Email
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Email\Block\Adminhtml\Config\System\Config;

use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;

/**
 * Render extension information html element in Stores Configuration
 *
 * @category MageTrend
 * @package  Magetend/Email
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Info extends \Magento\Config\Block\System\Config\Form\Field
{
    const MODULE_NAMESPACE = 'Magetrend_Email';

    const CONFIG_NAMESPACE = 'mtemail';

    const XML_PATH_GENERAL = 'mtemail/general/is_active';
    
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
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        array $data = []
    ) {
        $this->curl = $curl;
        $this->resourceConfig = $resourceConfig;
        $this->cacheManager = $context->getCache();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_renderValue($element);
        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    //@codingStandardsIgnoreLine
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td></td><td class="value" colspan="3">';
        $html .= $this->_getElementHtml($element);
        $html .= '</td>';
        return $html;
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    //@codingStandardsIgnoreLine
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        parent::_getElementHtml($element);
        $data = $this->prepareData();
        $html = '';
        if (isset($data['content']) && !empty($data['content'])) {
            $html = $data['content'];
            if (substr_count($html, 'script') > 0 || substr_count($html, 'iframe') > 0) {
                $html = '';
            }
        }
        return $html;
    }

    /**
     * Get extensions key status
     *
     * @return string
     */
    //@codingStandardsIgnoreLine
    protected function prepareData()
    {
        $response = $this->sendRequest();
        if (!$response) {
            return '';
        }
        $this->updateStatus($response);
        return $response['data'];
    }

    /**
     * Update Status
     * @param array $response
     */
    public function updateStatus($response)
    {
        if (isset($response['key'])
            && isset($response['key']['status'])
            && $response['key']['status'] == 1
        ) {
            return;
        }

        $this->resourceConfig->saveConfig(
            self::XML_PATH_GENERAL,
            0,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->cacheManager->clean([CacheTypeConfig::CACHE_TAG]);
    }

    /**
     * Sending Request
     * @return bool|array
     */
    public function sendRequest()
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
                    'module' => self::MODULE_NAMESPACE
                ]
            );

            $responseBody = json_decode($this->curl->getBody(), true);
            //@codingStandardsIgnoreEnd
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
        $stores = $this->_storeManager->getStores();
        $urlArray = [];
        if (!empty($stores)) {
            foreach ($stores as $store) {
                $urlArray[] = $this->_scopeConfig->getValue(
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
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->_scopeConfig->getValue(
            //@codingStandardsIgnoreLine
            self::CONFIG_NAMESPACE.base64_decode('L2xpY2Vuc2Uva2V5'),
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }
}
