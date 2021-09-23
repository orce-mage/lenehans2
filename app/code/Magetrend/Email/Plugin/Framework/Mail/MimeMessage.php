<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Framework\Mail;

use \Magetrend\Email\Helper\Data;
use \Magetrend\Email\Helper\Html2Text;

class MimeMessage
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
        $this->mtHelper = $helper;
        $this->html2Text = $html2Text;
        $this->productMetadata = $productMetadata;
    }

    public function afterGetParts($message, $parts)
    {
        if (!$this->mtHelper->isPlainTextVersionEnabled()) {
            return $parts;
        }

        if (version_compare($this->productMetadata->getVersion(), '2.3.3', '<=')) {
            return $parts;
        }

        $htmlPartKey = false;
        foreach ($parts as $key => $part) {
            /**
             * @var \Magento\Framework\Mail\MimePart $part
             */
            if (!$htmlPartKey && $part->getType() == \Zend\Mime\Mime::TYPE_HTML) {
                $htmlPartKey = $key;
            }

            if ($part->getType() == 'multipart/alternative') {
                return $parts;
            }
        }

        if ($htmlPartKey === false) {
            return $parts;
        }

        $htmlPart = $parts[$htmlPartKey];

        $this->html2Text->setHtml($htmlPart->getRawContent());
        $textVersion = $this->html2Text->getText();
        $charset = 'utf-8';
        $encoding = 'quoted-printable';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $textPart = $objectManager->create('Magento\Framework\Mail\MimePart', [
            'content' => $textVersion,
            'charset' => $charset,
            'type' => \Zend\Mime\Mime::TYPE_TEXT,
        ]);

        $alternatives = $objectManager->create('Zend\Mime\Message');
        $alternatives->setParts([$textPart, $htmlPart]);

        $alternativesPart = $objectManager->create('\Zend\Mime\Part', ['content' => $alternatives->generateMessage()]);
        $alternativesPart->setType('multipart/alternative');
        $alternativesPart->setBoundary($alternatives->getMime()->boundary());

        $parts[$htmlPartKey] = $alternativesPart;
        return $parts;
    }
}
