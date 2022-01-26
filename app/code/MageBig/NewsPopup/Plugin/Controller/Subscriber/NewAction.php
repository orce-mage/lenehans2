<?php

namespace MageBig\NewsPopup\Plugin\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Serialize\Serializer\Json;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Json
     */
    private $json;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param FormKeyValidator $formKeyValidator
     * @param Json $json
     * @param EmailValidator|null $emailValidator
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        FormKeyValidator $formKeyValidator,
        Json $json,
        EmailValidator $emailValidator = null
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->subscriptionManager = $subscriptionManager;
        $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailValidator::class);
        $this->formKeyValidator = $formKeyValidator;
        $this->json = $json;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement,
            $subscriptionManager,
            $emailValidator
        );
    }

    public function aroundExecute($subject, $proceed)
    {
        try {
            $this->validateRequest();
            $this->validateFormKey();

            $email = (string)$this->getRequest()->getPost('email');
            $this->validateEmailFormat($email);
            $this->validateGuestSubscription();
            $this->validateEmailAvailable($email);

            $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
            /** @var Subscriber $subscriber */
            $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
            if ($subscriber->getId()
                && (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                $message = __('This email address is already subscribed.');
                $this->jsonResponse(false, $message);
            } else {
                $storeId = (int)$this->_storeManager->getStore()->getId();
                $currentCustomerId = $this->getSessionCustomerId($email);
                $subscriber = $currentCustomerId
                    ? $this->subscriptionManager->subscribeCustomer($currentCustomerId, $storeId)
                    : $this->subscriptionManager->subscribe($email, $storeId);
                $message = $this->getSuccessMessage((int)$subscriber->getSubscriberStatus());
                $this->jsonResponse(true, $message);
            }
        } catch (LocalizedException $e) {
            $this->jsonResponse(false, $e->getMessage());
        } catch (\Exception $e) {
            $message = __('Something went wrong with the subscription.');
            $this->jsonResponse(false, $message);
        }
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @throws LocalizedException
     * @return void
     */
    protected function validateEmailAvailable($email)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($this->_customerSession->isLoggedIn()
            && ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
                && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId))
        ) {
            $message = __('This email address is already assigned to another user.');
            $this->jsonResponse(false, $message);
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws LocalizedException
     * @return void
     */
    protected function validateGuestSubscription()
    {
        if ($this->_objectManager->get(ScopeConfigInterface::class)
                ->getValue(
                    Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                    ScopeInterface::SCOPE_STORE
                ) != 1
            && !$this->_customerSession->isLoggedIn()
        ) {
            $message = __(
                'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                $this->_customerUrl->getRegisterUrl()
            );
            $this->jsonResponse(false, $message);
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws LocalizedException
     * @return void
     */
    protected function validateEmailFormat($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            $message = __('Please enter a valid email address.');
            $this->jsonResponse(false, $message);
        }
    }

    /**
     * Get customer id from session if he is owner of the email
     *
     * @param string $email
     * @return int|null
     */
    private function getSessionCustomerId(string $email): ?int
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return null;
        }

        $customer = $this->_customerSession->getCustomerDataObject();
        if ($customer->getEmail() !== $email) {
            return null;
        }

        return (int)$this->_customerSession->getId();
    }

    /**
     * Get success message
     *
     * @param int $status
     * @return Phrase
     */
    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }

    /**
     * JSON response builder.
     * @param bool $isSuccess
     * @param string $message
     */
    private function jsonResponse(bool $isSuccess = false, string $message = '')
    {
        $this->getResponse()->representJson(
            $this->json->serialize($this->getResponseData($isSuccess, $message))
        );
    }

    /**
     * Returns response data.
     *
     * @param string $error
     * @return array
     */
    private function getResponseData(bool $isSuccess, string $message = ''): array
    {
        $response = [
            'success' => $isSuccess,
            'message' => $message,
            'redirect' => false
        ];

        return $response;
    }

    /**
     * Validates the Request HTTP method
     *
     * @return void
     * @throws NotFoundException
     */
    private function validateRequest()
    {
        if ($this->getRequest()->isPost() === false) {
            throw new NotFoundException(__('Page Not Found'));
        }
    }

    /**
     * Validates form key
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateFormKey()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Invalid Form Key. Please refresh the page.')
            );
        }
    }
}
