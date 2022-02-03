<?php

namespace Amasty\Faq\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Faq section
 */
class Question implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $faqSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\Session\Generic $faqSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->faqSession = $faqSession;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = (array)$this->faqSession->getFormData(true);
        if (!isset($data['email']) || empty($data['email'])) {
            $data['email'] = $this->customerSession->getCustomer()->getEmail();
        }
        if (!isset($data['name']) || empty($data['name'])) {
            $data['name'] = $this->customerSession->getCustomer()->getFirstname();
        }
        if (!isset($data['title'])) {
            $data['title'] = '';
        }

        return $data;
    }
}
