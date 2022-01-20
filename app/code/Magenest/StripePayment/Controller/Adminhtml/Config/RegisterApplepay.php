<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class RegisterApplepay extends Action
{
    protected $stripeHelper;

    protected $configWriter;

    /**
     * @param Context $context
     */
    public function __construct(
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $writerInterface,
        Context $context
    ) {
        parent::__construct($context);
        $this->stripeHelper = $stripeHelper;
        $this->configWriter = $writerInterface;
    }

    public function execute()
    {
        $result = $this->resultFactory->create('json');
        try {
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $stripeConfig = $this->stripeHelper->getStripeConfig();
            $testSecretKey = $stripeConfig->getConfigValue('test_secret');
            $liveSecretKey = $stripeConfig->getConfigValue('live_secret');
            $domainName = $this->getRequest()->getParam('domain');
            if (!$domainName) {
                throw new LocalizedException(__("Domain name is require"));
            }
            $isSandbox = $stripeConfig->getIsSandboxMode();
            if ($isSandbox) {
                $responseTest = false;
                if ($testSecretKey) {
                    $registryTest = $this->stripeHelper->sendRequest(
                        ['domain_name' => $domainName],
                        "https://api.stripe.com/v1/apple_pay/domains",
                        "post",
                        $testSecretKey
                    );
                    if (isset($registryTest['id'])) {
                        $messageTestApi = __("Register api success");
                        $dataTest = json_encode($registryTest);
                        $this->configWriter->save("payment/magenest_stripe_applepay/apitest", $dataTest);
                        $responseTest = isset($registryTest['domain_name'])?$registryTest['domain_name']:"";
                    }
                    if (isset($registryTest['error']['message'])) {
                        $messageTestApi = $registryTest['error']['message'];
                    }
                } else {
                    $messageTestApi = __("Error: Test secret key is missing");
                }
                return $result->setData([
                    'error' => false,
                    'success' => true,
                    'message_test' => $messageTestApi,
                    'response_test' => $responseTest,
                ]);
            } else {
                $responseLive = false;
                if ($liveSecretKey) {
                    $registryLive = $this->stripeHelper->sendRequest(
                        ['domain_name' => $domainName],
                        "https://api.stripe.com/v1/apple_pay/domains",
                        "post",
                        $liveSecretKey
                    );
                    if (isset($registryLive['id'])) {
                        $messageLiveApi = "Register api success";
                        $dataLive = json_encode($registryLive);
                        $this->configWriter->save("payment/magenest_stripe_applepay/apilive", $dataLive);
                        $responseLive = isset($registryLive['domain_name'])?$registryLive['domain_name']:"";
                    }
                    if (isset($registryLive['error']['message'])) {
                        $messageLiveApi = $registryLive['error']['message'];
                    }
                } else {
                    $messageLiveApi = __("Error: Live secret key is missing");
                }
                return $result->setData([
                    'error' => false,
                    'success' => true,
                    'message_live' => $messageLiveApi,
                    'response_live' => $responseLive,
                ]);
            }
        } catch (\Exception $e) {
            return $result->setData([
                'error' => true,
                'message' => $e->getMessage(),
                'success' => false
            ]);
        }
    }
}
