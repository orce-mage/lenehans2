<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Email;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;

class UploadTransportBuilder extends TransportBuilder
{
    const TYPE_TEXT = 'text/plain';
    const TYPE_HTML = 'text/html';

    const PDF_FILE_NAME = 'Gift.pdf';
    const IMG_FILE_NAME = 'GiftCard.png';

    /**
     * @var MailMessageFactory
     */
    private $ammessageFactory;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var AddressConverter
     */
    private $addressConverter;

    /**
     * @var array
     */
    private $messageData;

    /**
     * @var array
     */
    private $attachments = [];

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory,
        File $fileDriver
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory
        );
        /** @var MailMessage message */
        $this->message = $message;
        $this->ammessageFactory = $messageFactory;
        $this->fileDriver = $fileDriver;
        if (interface_exists(MailMessageInterface::class)) {
            $this->message = $objectManager->create(MailMessage::class);
            $this->ammessageFactory = $objectManager->create(MailMessageFactory::class);
        }
        if (interface_exists(EmailMessageInterface::class)) {
            $this->mimeMessageInterfaceFactory = $objectManager->create(MimeMessageInterfaceFactory::class);
            $this->emailMessageInterfaceFactory = $objectManager->create(EmailMessageInterfaceFactory::class);
            $this->addressConverter = $objectManager->create(AddressConverter::class);
        }
    }

    public function getTransport()
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }

        return $mailTransport;
    }

    protected function prepareMessage()
    {
        parent::prepareMessage();

        if ($this->mimeMessageInterfaceFactory !== null) {
            $parts = $this->message->getBody()->getParts();

            $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
                ['parts' => array_merge($parts, $this->attachments)]
            );

            $this->messageData['subject'] = $this->message->getSubject();
            $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);
        }

        return $this;
    }

    /**
     * @param string $content
     *
     * @return UploadTransportBuilder
     */
    public function addAttachment(string $content): UploadTransportBuilder
    {
        if ($content) {
            $attachmentPart = $this->message->createAttachment(
                $content,
                'application/pdf',
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                self::PDF_FILE_NAME
            );

            $this->attachments[] = $attachmentPart;
        }

        return $this;
    }

    /**
     * @param string $file
     *
     * @return UploadTransportBuilder
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function attachFile(string $file): UploadTransportBuilder
    {
        if (!empty($file) && $this->fileDriver->isExists($file)) {
            $part = $this->message
                ->createAttachment(
                    $this->fileDriver->fileGetContents($file),
                    'IMAGE/PNG',
                    \Zend_Mime::DISPOSITION_INLINE,
                    \Zend_Mime::ENCODING_BASE64,
                    self::IMG_FILE_NAME
                );

            $this->attachments[] = $part;
        }

        return $this;
    }

    /**
     * @return UploadTransportBuilder
     */
    public function clear(): UploadTransportBuilder
    {
        $this->reset();

        return $this;
    }

    protected function reset()
    {
        parent::reset();
        $this->message = $this->ammessageFactory->create();
        $this->messageData = [];
        $this->attachments = [];

        return $this;
    }

    public function addTo($address, $name = '')
    {
        if ($this->mimeMessageInterfaceFactory !== null) {
            $this->addAddressByType('to', $address, $name);
            parent::addTo($address, $name);
        } else {
            $this->message->addTo($address, $name);
        }

        return $this;
    }

    /**
     * @param $fromAddress
     * @param null $scopeId
     * @return $this
     */
    public function setFrom($fromAddress, $scopeId = null)
    {
        $fromAddress = $this->_senderResolver->resolve($fromAddress, $scopeId);

        if ($this->mimeMessageInterfaceFactory !== null) {
            $this->addAddressByType('from', $fromAddress['email'], $fromAddress['name']);
            parent::setFrom($fromAddress);
        } else {
            $this->message->setFrom($fromAddress['email'], $fromAddress['name']);
        }

        return $this;
    }

    /**
     * @param string $addressType
     * @param string|array $email
     * @param string $name
     */
    private function addAddressByType(string $addressType, $email, string $name = '')
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        }
    }
}
