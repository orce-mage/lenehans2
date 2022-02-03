<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Controller\Checkout;

use Amasty\InvisibleCaptcha\Model\Captcha;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Validate extends Action implements HttpPostActionInterface
{
    /**
     * @var Captcha
     */
    private $captchaModel;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Context $context,
        Captcha $captchaModel,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->captchaModel = $captchaModel;
        $this->request = $request;
        $this->context = $context;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->request->isAjax() || !$this->request->isPost()) {
            $result->setData(['error' => true]);

            return $result;
        }

        $token = $this->request->getParam('g-recaptcha-response');
        if (!$token) {
            $result->setData(['error' => true, 'message' => __('Please provide right ReCaptcha token')]);

            return $result;
        }

        $validation = $this->captchaModel->verify($token);
        if (!$validation['success']) {
            $result->setData(['error' => true, 'message' => $validation['error']]);

            return $result;
        }

        $result->setData(['error' => false]);

        return $result;
    }
}
