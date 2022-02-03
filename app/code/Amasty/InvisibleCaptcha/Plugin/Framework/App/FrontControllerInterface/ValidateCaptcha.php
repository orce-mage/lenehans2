<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Plugin\Framework\App\FrontControllerInterface;

use Amasty\InvisibleCaptcha\Model\Captcha;
use Amasty\InvisibleCaptcha\Model\ConfigProvider;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class ValidateCaptcha
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * Captcha model instance
     *
     * @var Captcha
     */
    private $captchaModel;

    /**
     * @var Session
     */
    private $session;

    /**
     * Ignore list of URLs for logged in users
     *
     * @var array
     */
    private $ignoreListForLoggedIn = ['productalert/add'];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        UrlInterface $urlBuilder,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        ResponseFactory $responseFactory,
        Captcha $captchaModel,
        Session $session,
        ConfigProvider $configProvider
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->responseFactory = $responseFactory;
        $this->captchaModel = $captchaModel;
        $this->session = $session;
        $this->configProvider = $configProvider;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function aroundDispatch(
        FrontControllerInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        if ($this->captchaModel->isNeedToShowCaptcha()) {
            foreach ($this->configProvider->getAllUrls() as $captchaUrl) {
                if ($request->isPost()
                    && !$this->isInIgnoreList($captchaUrl)
                    && false !== strpos($this->urlBuilder->getCurrentUrl(), $captchaUrl)
                ) {
                    $token = $request->getPost('g-recaptcha-response');
                    $validation = $this->captchaModel->verify($token);

                    if (!$validation['success']) {
                        $this->messageManager->addErrorMessage($validation['error']);
                        $response = $this->responseFactory->create();
                        $response->setRedirect($this->redirect->getRefererUrl());
                        $response->setNoCacheHeaders();

                        return $response;
                    }

                    break;
                }
            }
        }

        return $proceed($request);
    }

    /**
     * @param string $captchaUrl
     * @return bool
     */
    private function isInIgnoreList(string $captchaUrl): bool
    {
        if ($this->session->getCustomerGroupId() != Group::NOT_LOGGED_IN_ID) {
            foreach ($this->ignoreListForLoggedIn as $ignoredUrl) {
                if (false !== strpos($captchaUrl, $ignoredUrl)) {
                    return true;
                }
            }
        }

        return false;
    }
}
