<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Widget;

use Amasty\GiftCardAccount\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class CodeStatusChecker extends Template implements BlockInterface
{
    protected $_template = "widget/status/checker.phtml";

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function toHtml()
    {
        if (!$this->configProvider->isEnabled()) {
            return '';
        }

        return parent::toHtml();
    }
}
