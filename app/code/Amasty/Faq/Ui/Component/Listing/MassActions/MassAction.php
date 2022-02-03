<?php

namespace Amasty\Faq\Ui\Component\Listing\MassActions;

class MassAction extends \Magento\Ui\Component\Action
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Data\OptionSourceInterface
     */
    private $optionSource;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Data\OptionSourceInterface $optionSource = null,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);
        $this->optionSource = $optionSource;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare params array for urlBuilder
     *
     * @param string|int $optionValue
     *
     * @return array
     */
    public function getUrlParams($optionValue)
    {
        return ['key' => $optionValue];
    }

    /**
     * Complete Mass actions with external options
     */
    public function prepare()
    {
        $options = $this->optionSource->toOptionArray();
        foreach ($options as $option) {
            $this->actions[] = [
                'type' => strtolower($option['label']),
                'label' => $option['label'],
                'url' => $this->urlBuilder->getUrl(
                    $this->_data['config']['massActionUrl'],
                    $this->getUrlParams($option['value'])
                )
            ];
        }
        parent::prepare();
    }
}
