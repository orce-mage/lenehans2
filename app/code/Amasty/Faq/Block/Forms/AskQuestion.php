<?php

namespace Amasty\Faq\Block\Forms;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Customer\Model\Context as HttpContext;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class AskQuestion extends \Amasty\Faq\Block\AbstractBlock implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->configProvider = $configProvider;
    }

    /**
     * Checks if user could receive email with answer
     *
     * @return bool
     */
    public function isNotifyUser()
    {
        return $this->configProvider->isNotifyUser();
    }

    /**
     * Get url for save on front
     *
     * @return string
     */
    public function getUrlAction()
    {
        return $this->_urlBuilder->getUrl('faq/index/save');
    }

    /**
     * Check is customer login in
     *
     * @return bool
     */
    public function isCustomerLoginIn()
    {
         return (bool)$this->getHttpContext()->getValue(HttpContext::CONTEXT_AUTH);
    }

    /**
     * Check is allow unregistered customers ask
     *
     * @return bool
     */
    public function isAllowUnregisteredCustomersAsk()
    {
        return $this->configProvider->isAllowUnregisteredCustomersAsk();
    }

    /**
     * @return array|null
     */
    public function getAdditionalField()
    {
        if ($product = $this->coreRegistry->registry('current_product')) {
            return ['field' => 'product_ids', 'value' => (int)$product->getId()];
        } elseif ($categoryId = (int)$this->coreRegistry->registry('current_faq_category_id')) {
            return ['field' => 'category_ids', 'value' => $categoryId];
        }

        return null;
    }

    /**
     * Check if GDPR consent enabled
     *
     * @return bool
     */
    public function isGDPREnabled()
    {
        return $this->configProvider->isGDPREnabled();
    }

    /**
     * Get text for GDPR
     *
     * @return string
     */
    public function getGDPRText()
    {
        return $this->configProvider->getGDPRText();
    }

    public function getIdentities()
    {
        return [\Magento\Customer\Model\Cache\Type\Notification::CACHE_TAG];
    }
}
