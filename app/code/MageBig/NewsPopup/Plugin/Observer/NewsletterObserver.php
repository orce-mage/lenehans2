<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageBig\NewsPopup\Plugin\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\InputException;
use Magento\ReCaptchaCustomer\Model\AjaxLogin\ErrorProcessor;
use Magento\ReCaptchaUi\Model\CaptchaResponseResolverInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * NewsletterObserver
 */
class NewsletterObserver
{
    /**
     * @var CaptchaResponseResolverInterface
     */
    private $captchaResponseResolver;

    /**
     * @var ValidationConfigResolverInterface
     */
    private $validationConfigResolver;

    /**
     * @var ValidatorInterface
     */
    private $captchaValidator;

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isCaptchaEnabled;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorProcessor
     */
    private $errorProcessor;

    /**
     * @param CaptchaResponseResolverInterface $captchaResponseResolver
     * @param ValidationConfigResolverInterface $validationConfigResolver
     * @param ValidatorInterface $captchaValidator
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     * @param LoggerInterface $logger
     * @param ErrorProcessor $errorProcessor
     */
    public function __construct(
        CaptchaResponseResolverInterface $captchaResponseResolver,
        ValidationConfigResolverInterface $validationConfigResolver,
        ValidatorInterface $captchaValidator,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        LoggerInterface $logger,
        ErrorProcessor $errorProcessor
    ) {
        $this->captchaResponseResolver = $captchaResponseResolver;
        $this->validationConfigResolver = $validationConfigResolver;
        $this->captchaValidator = $captchaValidator;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->logger = $logger;
        $this->errorProcessor = $errorProcessor;
    }

    /**
     * @param $subject
     * @param $procedd
     * @param $observer
     * @throws InputException
     */
    public function aroundExecute($subject, $proceed, $observer)
    {
        $key = 'newsletter';
        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
            /** @var Action $controller */
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $response = $controller->getResponse();

            $validationConfig = $this->validationConfigResolver->get($key);

            try {
                $reCaptchaResponse = $this->captchaResponseResolver->resolve($request);
                //$this->logger->info($reCaptchaResponse);
            } catch (InputException $e) {
                //$this->logger->error($e);
                $this->errorProcessor->processError(
                    $response,
                    [],
                    $key
                );
                return;
            }

            $validationResult = $this->captchaValidator->isValid($reCaptchaResponse, $validationConfig);
            if (false === $validationResult->isValid()) {
                $this->errorProcessor->processError(
                    $response,
                    $validationResult->getErrors(),
                    $key
                );
            }
        }
    }
}
