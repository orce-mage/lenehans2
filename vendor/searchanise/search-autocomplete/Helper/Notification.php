<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Searchanise notifications
 */
class Notification extends AbstractHelper
{
    const TYPE_ERROR   = 'E';
    const TYPE_WARNING = 'W';
    const TYPE_NOTICE  = 'N';
    const TYPE_SUCCESS = 'S';

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;

        parent::__construct($context);
    }

    /**
     * Set notification message
     *
     * @param string $type    notification type (E - error, W - warning, N - notice, S - success)
     * @param string $title   notification title
     * @param string $message notification message
     */
    public function setNotification($type = self::TYPE_NOTICE, $title = '', $message = '')
    {
        if (!empty($title)) {
            $message = $title . ': ' . $message;
        }

        switch ($type) {
            case self::TYPE_SUCCESS:
                $this->messageManager->addSuccess($message);
                break;
            case self::TYPE_WARNING:
                $this->messageManager->addWarning($message);
                break;
            case self::TYPE_ERROR:
                $this->messageManager->addError($message);
                break;
            default:
                $this->messageManager->addNotice($message);
        }

        return true;
    }
}
