<?php

namespace MageBig\WidgetPlus\Model\Plugin\Adminhtml\Wysiwyg\Images;

class Content
{
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function afterGetOnInsertUrl(\Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content $subject, $result)
    {
        // $this->logger->debug($subject->getRequest()->getParam('target_element_id'));
        if (strpos($subject->getRequest()->getParam('target_element_id'), 'background_')) {
            return $subject->getUrl('cms/*/onInsert', ['is_background' => true]);
        }

        return $result;
    }
}
