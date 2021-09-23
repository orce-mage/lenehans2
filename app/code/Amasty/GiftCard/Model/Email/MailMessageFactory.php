<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Email;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Amasty\GiftCard\Model\MailMessage
 */
class MailMessageFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = \Amasty\GiftCard\Model\Email\MailMessage::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     *
     * @return \Amasty\GiftCard\Model\Email\MailMessage
     */
    public function create(array $data = []): \Amasty\GiftCard\Model\Email\MailMessage
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
