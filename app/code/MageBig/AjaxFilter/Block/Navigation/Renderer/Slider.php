<?php

namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

use MageBig\AjaxFilter\Model\Layer\Filter\Decimal;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Json\EncoderInterface;
use \Magento\Catalog\Helper\Data as CatalogHelper;

class Slider extends AbstractRenderer
{
    /**
     * The Data role, used for Javascript mapping of slider Widget
     *
     * @var string
     */
    protected $dataRole = "range-slider";

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     *
     * @param Context $context Template context.
     * @param CatalogHelper $catalogHelper Catalog helper.
     * @param EncoderInterface $jsonEncoder JSON Encoder.
     * @param FormatInterface $localeFormat Price format config.
     * @param array $data Custom data.
     */
    public function __construct(
        Context $context,
        CatalogHelper $catalogHelper,
        EncoderInterface $jsonEncoder,
        FormatInterface $localeFormat,
        array $data = []
    ) {
        parent::__construct($context, $catalogHelper, $data);

        $this->jsonEncoder = $jsonEncoder;
        $this->localeFormat = $localeFormat;
    }

    /**
     * Return the config of the price slider JS widget.
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $config = $this->getConfig();

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Retrieve the data role
     *
     * @return string
     */
    public function getDataRole()
    {
        $filter = $this->getFilter();

        return $this->dataRole . "-" . $filter->getRequestVar();
    }

    /**
     * {@inheritDoc}
     */
    protected function canRenderFilter()
    {
        return $this->getFilter() instanceof Decimal;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFieldFormat()
    {
        $format = $this->localeFormat->getPriceFormat();

        $attribute = $this->getFilter()->getAttributeModel();

        $format['pattern'] = (string)$attribute->getDisplayPattern();
        $format['precision'] = (int)$attribute->getDisplayPrecision();
        $format['requiredPrecision'] = (int)$attribute->getDisplayPrecision();
        $format['integerRequired'] = (int)$attribute->getDisplayPrecision() > 0;

        return $format;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getConfig()
    {
        $config = [
            'minValue' => $this->getMinValue(),
            'maxValue' => $this->getMaxValue(),
            'currentValue' => $this->getCurrentValue(),
            'fieldFormat' => $this->getFieldFormat(),
            'actionUrl' => $this->getActionUrl(),
            'code' => $this->getCode()
        ];

        return $config;
    }

    /**
     * Returns min value of the slider.
     *
     * @return int
     */
    public function getMinValue()
    {
        return $this->getFilter()->getMinValue();
    }

    /**
     * Returns max value of the slider.
     *
     * @return int
     */
    public function getMaxValue()
    {
        return $this->getFilter()->getMaxValue() + 1;
    }

    /**
     * Returns values currently selected by the user.
     *
     * @return array
     */
    public function getCurrentValue()
    {
        $currentValue = $this->getFilter()->getCurrentValue();

        if (!is_array($currentValue)) {
            $currentValue = [];
        }

        if (!isset($currentValue['from']) || $currentValue['from'] === '') {
            $currentValue['from'] = $this->getMinValue();
        }

        if (!isset($currentValue['to']) || $currentValue['to'] === '') {
            $currentValue['to'] = $this->getMaxValue();
        }

        return $currentValue;
    }
}
