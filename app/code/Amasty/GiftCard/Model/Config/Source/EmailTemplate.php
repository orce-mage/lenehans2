<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


declare(strict_types=1);

namespace Amasty\GiftCard\Model\Config\Source;

use Magento\Config\Model\Config\Source\Email\Template;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Module\Manager;

class EmailTemplate extends AbstractSource
{
    const DEFAULT_EMAIL_TEMPLATE = 'amgiftcard_email_email_template';

    /**
     * @var Template
     */
    private $templates;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Template $templates,
        Manager $moduleManager
    ) {
        $this->templates = $templates;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->moduleManager->isEnabled('Amasty_GiftCard')) {
            return [];
        }

        $this->templates->setPath(self::DEFAULT_EMAIL_TEMPLATE);

        return $this->templates->toOptionArray();
    }
}
