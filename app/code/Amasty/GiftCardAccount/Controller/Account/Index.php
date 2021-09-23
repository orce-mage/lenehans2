<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Account;

use Amasty\GiftCard\Model\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Session $session,
        ConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        if (!$this->configProvider->isEnabled()) {
            throw new NotFoundException(__('Invalid Request'));
        }
        /** @var \Magento\Backend\Model\View\Result\Page $pageResult */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Cards'));

        if (!$this->session->getCustomerId()) {
            $this->session->start();
        }

        if ($this->session->isLoggedIn()) {
            return $resultPage;
        } else {
            return $this->_redirect('customer/account/login');
        }
    }
}
