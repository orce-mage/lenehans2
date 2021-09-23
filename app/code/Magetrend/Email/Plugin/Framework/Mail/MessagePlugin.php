<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Framework\Mail;

use Magento\Framework\Mail\Message;
use \Magetrend\Email\Helper\Data;
use \Magetrend\Email\Helper\Html2Text;

class MessagePlugin
{
    /**
     * @var \Magetrend\Email\Helper\Data
     */
    public $mtHelper;

    /**
     * @var Html2Text
     */
    public $html2Text;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    public $productMetadata;

    /**
     * MessagePlugin constructor.
     *
     * @param Data $helper
     * @param Html2Text $html2Text
     */
    public function __construct(
        Data $helper,
        Html2Text $html2Text,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
        $this->mtHelper = $helper;
        $this->html2Text = $html2Text;
    }

    public function aroundSetBodyHtml(Message $message, callable $parent, $body, $charset = null, $encoding = 'quoted-printable')
    {
        $encoding = 'quoted-printable';
        if (!$this->mtHelper->isPlainTextVersionEnabled()) {
            return $parent($body, $charset, $encoding);
        }

        if (version_compare($this->productMetadata->getVersion(), '2.3.3', '>')) {
            return $parent($body, $charset, $encoding);
        }

        $message->setMessageType(\Magento\Framework\Mail\MessageInterface::TYPE_HTML);
        $this->html2Text->setHtml($body);
        $textVersion = $this->html2Text->getText();
        $charset = 'utf-8';

        $htmlPart = new \Zend\Mime\Part($body);
        $htmlPart->setCharset($charset);
        $htmlPart->setType(\Zend\Mime\Mime::TYPE_HTML);
        $htmlPart->setEncoding($encoding);

        $textPart = new \Zend\Mime\Part($textVersion);
        $textPart->setCharset($charset);
        $textPart->setType(\Zend\Mime\Mime::TYPE_TEXT);
        $textPart->setEncoding($encoding);

        $alternatives = new \Zend\Mime\Message();
        $alternatives->setParts([$textPart, $htmlPart]);

        $alternativesPart = new \Zend\Mime\Part($alternatives->generateMessage());
        $alternativesPart->setType('multipart/alternative');
        $alternativesPart->setBoundary($alternatives->getMime()->boundary());

        $body = new \Zend\Mime\Message();
        $body->addPart($alternativesPart);

        $message->setBody($body);
        return $message;
    }
}
