<?php

namespace Amasty\Faq\Controller\Stat;

use Amasty\Faq\Api\VisitStatRepositoryInterface;
use Amasty\Faq\Model\VisitStatFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Collect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var VisitStatRepositoryInterface
     */
    private $visitStatRepository;

    /**
     * @var VisitStatFactory
     */
    private $visitStatFactory;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Page\Config $pageConfig,
        VisitStatRepositoryInterface $visitStatRepository,
        VisitStatFactory $visitStatFactory,
        Visitor $visitor,
        CustomerSession $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->pageConfig = $pageConfig;
        $this->visitStatRepository = $visitStatRepository;
        $this->visitStatFactory = $visitStatFactory;
        $this->visitor = $visitor;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['search_query'])) {
            return;
        }
        $visitStat = $this->visitStatFactory->create();
        /** @var \Amasty\Faq\Model\VisitStat $visitStat */
        $visitStat->addData($params);
        if ($this->customerSession->getCustomerId()) {
            $visitStat->setCustomerId($this->customerSession->getCustomerId());
        } else {
            $visitStat->setVisitorId($this->visitor->getId());
        }
        $visitStat->setStoreId($this->storeManager->getStore()->getId());

        try {
            $this->visitStatRepository->save($visitStat);
        } catch (LocalizedException $e) {
            // do nothing
            null;
        }
    }
}
