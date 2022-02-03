<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Block\Adminhtml\System\Config\Field;

use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class AmastyCustomForms extends Field
{
    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Template\Context $context,
        ModuleInfoProvider $moduleInfoProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $url = "https://amasty.com/custom-form-for-magento-2.html"
            . "?utm_source=extension&utm_medium=link&utm_campaign=captcham2-cform2";

        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            $url = "https://marketplace.magento.com/amasty-module-magento-custom-form.html";
        }

        //Because it's necessary for codesniffer
        //phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
        $html = "Create customizable forms to collect additional information about your customers and"
            . " view the received data from the admin panel. "
            . "Organize questions into seo optimized, responsive and easy to navigate knowledge base. <a href='"
            . $url . "' target='_blank'>Learn more</a>.";

        $element->setComment($html);

        return parent::render($element);
    }
}
