<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Template;

use Magetrend\Email\Model\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Template data
     *
     * @var array
     */
    protected $templateData = [];

    private $messageData = [];

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * @param $template
     */
    protected function setTemplateFilter($template)
    {
        if (isset($this->templateData['template_filter'])) {
            $template->setTemplateFilter($this->templateData['template_filter']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareMessage()
    {

        $part = ['type' =>  \Magento\Framework\Mail\MessageInterface::TYPE_HTML];
        $mimePart = $this->objectManager->create(
            'Magento\Framework\Mail\MimePartInterface',
            ['content' => $this->templateData['template_text']]
        );

        $bodyPart = $this->objectManager->create(
            'Magento\Framework\Mail\MimeMessageInterface',
            ['parts' => [$mimePart]]
        );

        $this->messageData['body'] = $bodyPart;
            $this->messageData['subject'] = html_entity_decode(
            (string)$this->templateData['template_subject'],
            ENT_QUOTES
        );

        $this->message = $this->objectManager->create(
            'Magento\Framework\Mail\EmailMessageInterface',
            $this->messageData
        );

        return $this;
    }


    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType($addressType, $email, $name = null)
    {
        $addressConverter = $this->objectManager->get('Magento\Framework\Mail\AddressConverter');
        if (is_string($email)) {
            $this->messageData[$addressType][] = $addressConverter->convert($email, $name);
            return;
        }

        $convertedAddressArray = $addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        }
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     */
    public function addCc($address, $name = '')
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addBcc($address)
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setReplyTo($email, $name = null)
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     *
     * @return $this
     * @throws InvalidArgumentException
     * @see setFromByScope()
     *
     * @deprecated 102.0.1 This function sets the from address but does not provide
     * a way of setting the correct from addresses based on the scope.
     */
    public function setFrom($from)
    {
        return $this->setFromByScope($from);
    }

    /**
     * Set mail from address by scopeId
     *
     * @param string|array $from
     * @param string|int $scopeId
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws MailException
     * @since 102.0.1
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $this->objectManager->get('Magento\Framework\Mail\Template\SenderResolverInterface');
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);
        return $this;
    }
}
