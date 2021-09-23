<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Customer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccountFormatter;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Cards extends Template
{
    /**
     * @var GiftCardAccountFormatter
     */
    private $accountFormatter;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        Context $context,
        GiftCardAccountFormatter $accountFormatter,
        Session $session,
        Repository $accountRepository,
        Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->accountFormatter = $accountFormatter;
        $this->serializer = $serializer;
        $this->session = $session;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCardsFront(): string
    {
        $cards = $this->accountRepository->getAccountsByCustomerId((int)$this->session->getCustomerId());
        $preparedCards = [];

        foreach ($cards as $card) {
            $preparedCards[] = $this->accountFormatter->getFormattedData($card);
        }

        return $this->serializer->serialize($preparedCards);
    }
}
