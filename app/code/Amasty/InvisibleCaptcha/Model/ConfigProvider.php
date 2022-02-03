<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\InvisibleCaptcha\Model\Config\Source\CaptchaVersion;
use Amasty\InvisibleCaptcha\Model\Config\Source\DefaultForms;
use Amasty\InvisibleCaptcha\Model\Config\Source\Extension as ExtensionSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    /**#@+
     * Constants defined for xpath of system configuration
     */
    const CONFIG_PATH_GENERAL_ENABLE_MODULE = 'general/enabledCaptcha';
    const CONFIG_PATH_GENERAL_ENABLE_FOR_GUESTS_ONLY = 'general/enabledCaptchaForGuestsOnly';
    const CONFIG_PATH_GENERAL_WHITELIST_IP = 'general/ipWhiteList';

    const CONFIG_PATH_SETUP_CAPTCHA_VERSION = 'setup/captchaVersion';
    const CONFIG_PATH_SETUP_CAPTCHA_SCORE = 'setup/captchaScore';
    const CONFIG_PATH_SETUP_CAPTCHA_ERROR_MESSAGE = 'setup/errorMessage';
    const CONFIG_PATH_SETUP_SITE_KEY = 'setup/captchaKey';
    const CONFIG_PATH_SETUP_SECRET_KEY = 'setup/captchaSecret';
    const CONFIG_PATH_SETUP_SITE_KEY_V3 = 'setup/captchaKeyV3';
    const CONFIG_PATH_SETUP_SECRET_KEY_V3 = 'setup/captchaSecretV3';
    const CONFIG_PATH_SETUP_BADGE_POSITION = 'setup/badgePosition';
    const CONFIG_PATH_SETUP_BADGE_THEME = 'setup/badgeTheme';
    const CONFIG_PATH_SETUP_LANGUAGE = 'setup/captchaLanguage';

    const CONFIG_PATH_FORMS_DEFAULT_FORMS = 'forms/defaultForms';
    const CONFIG_PATH_FORMS_URLS = 'forms/urls';
    const CONFIG_PATH_FORMS_SELECTORS = 'forms/selectors';
    const CONFIG_PATH_AMASTY_CUSTOM_FORM = 'amasty/customForm';

    const CONFIG_PATH_INTEGRATIONS = 'amasty';
    /**#@-*/

    const FORM_SELECTOR_PATTERN = 'form[action*="%s"]';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string
     */
    protected $pathPrefix = 'aminvisiblecaptcha/';

    /**
     * Amasty extension URLs to validate
     *
     * @var array
     */
    private $additionalURLs = [];

    /**
     * Amasty extension form selectors
     *
     * @var array
     */
    private $additionalSelectors = [];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ModuleManager $moduleManager,
        DataObject $extensionsData,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($scopeConfig);
        $this->encryptor = $encryptor;

        foreach ($extensionsData->getData() as $configId => $data) {
            if ($this->isIntegrationEnabled($configId)
                && $moduleManager->isEnabled($data['name'])
            ) {
                $this->additionalURLs[] = $data['url'];
                $this->additionalSelectors[] = $data['selector'];
            }
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isSetFlag(self::CONFIG_PATH_GENERAL_ENABLE_MODULE);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     * @return bool
     */
    public function isConfigured($storeId = null): bool
    {
        return !empty($this->getSiteKey($storeId)) && !empty($this->getSecretKey($storeId));
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return int
     */
    public function getCaptchaVersion($storeId = null): int
    {
        return (int)$this->getValue(self::CONFIG_PATH_SETUP_CAPTCHA_VERSION, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return float
     */
    public function getCaptchaScore($storeId = null): float
    {
        return (float)$this->getValue(self::CONFIG_PATH_SETUP_CAPTCHA_SCORE, $storeId) ?: 0.0;
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getConfigErrorMessage($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_SETUP_CAPTCHA_ERROR_MESSAGE, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return bool
     */
    public function isEnabledForGuestsOnly($storeId = null): bool
    {
        return $this->isSetFlag(self::CONFIG_PATH_GENERAL_ENABLE_FOR_GUESTS_ONLY, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getSiteKey($storeId = null)
    {
        $configPath = self::CONFIG_PATH_SETUP_SITE_KEY;
        if ($this->getCaptchaVersion() == CaptchaVersion::VERSION_3) {
            $configPath = self::CONFIG_PATH_SETUP_SITE_KEY_V3;
        }

        return $this->getValue($configPath, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getSecretKey($storeId = null)
    {
        $configPath = self::CONFIG_PATH_SETUP_SECRET_KEY;
        if ($this->getCaptchaVersion() == CaptchaVersion::VERSION_3) {
            $configPath = self::CONFIG_PATH_SETUP_SECRET_KEY_V3;
        }

        $value = $this->getValue($configPath, $storeId);
        if ($value) {
            $value = $this->encryptor->decrypt($value);
        }

        return $value;
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getBadgePosition($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_SETUP_BADGE_POSITION, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getCustomFormOption($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_AMASTY_CUSTOM_FORM, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getBadgeTheme($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_SETUP_BADGE_THEME, $storeId);
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return string
     */
    public function getLanguage($storeId = null): string
    {
        $language = $this->getValue(self::CONFIG_PATH_SETUP_LANGUAGE, $storeId);
        if ($language && 7 > mb_strlen($language)) {
            $language = 'hl=' . $language;
        } else {
            $language = '';
        }

        return $language;
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return array
     */
    public function getWhiteIps($storeId = null): array
    {
        return $this->explode($this->getValue(self::CONFIG_PATH_GENERAL_WHITELIST_IP, $storeId));
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return array
     */
    public function getCustomSelectors($storeId = null): array
    {
        return $this->explode($this->getValue(self::CONFIG_PATH_FORMS_SELECTORS, $storeId));
    }

    /**
     * @param int|ScopeInterface|null $storeId
     *
     * @return array
     */
    public function getCustomUrls($storeId = null): array
    {
        return $this->explode($this->getValue(self::CONFIG_PATH_FORMS_URLS, $storeId));
    }

    /**
     * @param int|ScopeInterface|null $storeId
     * @return array
     */
    public function getEnabledDefaultForms($storeId = null): array
    {
        return $this->explode($this->getValue(self::CONFIG_PATH_FORMS_DEFAULT_FORMS, $storeId));
    }

    /**
     * @param string $moduleConfigId
     * @param int|ScopeInterface|null $storeId
     * @return bool
     */
    public function isIntegrationEnabled(string $moduleConfigId, $storeId = null): bool
    {
        $integrationStatus = (int)$this->getValue(self::CONFIG_PATH_INTEGRATIONS . '/' . $moduleConfigId, $storeId);

        return $integrationStatus === ExtensionSource::INTEGRATION_ENABLED;
    }

    /**
     * @param int|ScopeInterface|null $storeId
     * @return array
     */
    public function getAllFormSelectors($storeId = null): array
    {
        $defaultFormsSelectors = array_map(
            function ($url) {
                return sprintf(self::FORM_SELECTOR_PATTERN, $url);
            },
            $this->getEnabledDefaultForms($storeId)
        );

        return array_merge(
            $this->getCustomSelectors($storeId),
            $defaultFormsSelectors,
            $this->additionalSelectors
        );
    }

    /**
     * @param int|ScopeInterface|null $storeId
     * @return boolean
     */
    public function isCaptchaOnPayments($storeId = null): bool
    {
        return in_array(DefaultForms::CHECKOUT_PAYMENTS, $this->getEnabledDefaultForms($storeId));
    }

    /**
     * @param int|ScopeInterface|null $storeId
     * @return array
     */
    public function getAllUrls($storeId = null): array
    {
        return array_merge(
            $this->getCustomUrls($storeId),
            $this->getEnabledDefaultForms($storeId),
            $this->additionalURLs
        );
    }

    /**
     * @param string|null $string
     * @return array
     */
    protected function explode($string): array
    {
        return !empty($string)
            ? preg_split('|\s*[\r\n,]+\s*|', trim($string), -1, PREG_SPLIT_NO_EMPTY)
            : [];
    }
}
