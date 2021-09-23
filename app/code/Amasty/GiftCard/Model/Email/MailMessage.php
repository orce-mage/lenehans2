<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Email;

use Zend\Mime\Mime;
use Zend\Mime\Part;

/**
 * Compatibility with Zend Framework 2 (Magento 2.3+)
 */
class MailMessage extends \Magento\Framework\Mail\Message
{
    /**
     * @var \Zend\Mail\Message
     */
    protected $zendMessage;

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * Initialize dependencies.
     *
     * @param string $charset
     */
    public function __construct($charset = 'utf-8')
    {
        $this->zendMessage = new \Zend\Mail\Message();
        $this->zendMessage->setEncoding($charset);
    }

    /**
     * @param string $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param string $filename
     *
     * @return Part
     */
    public function createAttachment(
        $body,
        $mimeType = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64,
        $filename = null
    ) {
        $this->setMessageType(self::TYPE_HTML);
        $attachment = new Part($body);
        $attachment->encoding = $encoding;
        $attachment->type = $mimeType;
        $attachment->disposition = $disposition;
        $attachment->filename = $filename;
        $this->attachments[] = $attachment;

        if ($this->getBody() instanceof \Zend\Mime\Message) {
            $this->getBody()->addPart($attachment);
        }

        return $attachment;
    }

    /**
     * @inheritDoc
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setBody($body)
    {
        if (is_string($body)) {
            $body = self::createHtmlMimeFromString($body);
        }
        $this->zendMessage->setBody($body);

        return $this;
    }

    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);

        return $this;
    }

    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    public function setFrom($fromAddress, $fromName = null)
    {
        $this->setFromAddress($fromAddress, $fromName);
        return $this;
    }

    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    public function addTo($toAddress, $name = null)
    {
        $this->zendMessage->addTo($toAddress);

        return $this;
    }

    public function addCc($ccAddress, $name = null)
    {
        $this->zendMessage->addCc($ccAddress);

        return $this;
    }

    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);

        return $this;
    }

    public function setReplyTo($replyToAddress, $name = null)
    {
        $this->zendMessage->setReplyTo($replyToAddress);

        return $this;
    }

    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * Create HTML mime message from the string.
     *
     * @param string $htmlBody
     *
     * @return \Zend\Mime\Message
     */
    private function createHtmlMimeFromString(string $htmlBody): \Zend\Mime\Message
    {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->zendMessage->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);

        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                $mimeMessage->addPart($attachment);
            }
        }

        return $mimeMessage;
    }
}
