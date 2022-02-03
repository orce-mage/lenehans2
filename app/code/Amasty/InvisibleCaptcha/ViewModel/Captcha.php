<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\ViewModel;

use Amasty\InvisibleCaptcha\Model\Captcha as CaptchaModel;
use Amasty\InvisibleCaptcha\Model\ConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Captcha implements ArgumentInterface
{
    /**
     * @var CaptchaModel
     */
    private $captchaModel;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        CaptchaModel $captchaModel,
        ConfigProvider $configProvider,
        UrlInterface $url
    ) {
        $this->captchaModel = $captchaModel;
        $this->configProvider = $configProvider;
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isNeedToShowCaptcha(): bool
    {
        return $this->captchaModel->isNeedToShowCaptcha();
    }

    /**
     * @return array
     */
    public function getAllFormSelectors(): array
    {
        return $this->configProvider->getAllFormSelectors();
    }

    /**
     * @return boolean
     */
    public function isCaptchaOnPayments(): bool
    {
        return $this->configProvider->isCaptchaOnPayments();
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->configProvider->getLanguage();
    }

    /**
     * @return string
     */
    public function getBadgeTheme()
    {
        return $this->configProvider->getBadgeTheme();
    }

    /**
     * @return string
     */
    public function getBadgePosition()
    {
        return $this->configProvider->getBadgePosition();
    }

    /**
     * @return string
     */
    public function getSiteKey()
    {
        return $this->configProvider->getSiteKey();
    }

    /**
     * @return string
     */
    public function getCheckoutValidateCaptchaUrl(): string
    {
        return $this->url->getUrl('amcapthca/checkout/validate');
    }

    /**
     * @return string
     */
    public function getInvisibleCaptchaCustomForm(): string
    {
        return $this->configProvider->getCustomFormOption();
    }
}
