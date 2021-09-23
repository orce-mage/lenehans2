<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Model\ConfigHtmlConverter\VariablesRendererInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class ConfigHtmlConverter
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var BaseImageLocation
     */
    private $baseImageLocation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * @var VariablesRendererInterface[]
     */
    private $variableRenderers;

    public function __construct(
        ConfigProvider $configProvider,
        Escaper $escaper,
        FilterProvider $filterProvider,
        LoggerInterface $logger,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        UrlInterface $urlBuilder,
        BaseImageLocation $baseImageLocation,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        array $variableRenderers = []
    ) {
        $this->configProvider = $configProvider;
        $this->escaper = $escaper;
        $this->filterProvider = $filterProvider;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->baseImageLocation = $baseImageLocation;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->variableRenderers = $variableRenderers;
    }

    /**
     * @param Location $location
     */
    public function setHtml($location)
    {
        $this->location = $location;
        $this->location->setPhoto($this->baseImageLocation->getMainImageUrl($location));
        try {
            $this->location->setStoreListHtml($this->dataHelper->compressHtml($this->getStoreListHtml()));
            $this->location->setPopupHtml($this->dataHelper->compressHtml($this->getPopupHtml()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Get store list html
     */
    private function getStoreListHtml()
    {
        $storeListTemplate = $this->configProvider->getStoreListTemplate();

        return $this->replaceLocationValues($storeListTemplate);
    }

    /**
     * Get popup html
     */
    private function getPopupHtml()
    {
        $baloon = $this->configProvider->getLocatorTemplate();

        return $this->replaceLocationValues($baloon);
    }

    /**
     * Return html with replaced values
     *
     * @todo improve logic
     * @param string $template
     * @return string $html
     */
    private function replaceLocationValues($template)
    {
        $locationData = $this->location->getData();
        $template = preg_replace_callback(
            '/{{if(?\'if\'.*)}}(.*|\n)*?{{\/\if(?P=if)}}/mixU',
            function ($match) use ($locationData) {
                if (!empty($locationData[$match['if']])) {
                    $value = $this->getPreparedValue($match['if']);

                    return str_replace(
                        [
                            '{{' . $match['if'] . '}}',
                            '{{if' . $match['if'] . '}}',
                            '{{/if' . $match['if'] . '}}'
                        ],
                        [$value, '', ''],
                        $match['0']
                    );
                }

                return '';
            },
            $template
        );

        $html = preg_replace_callback(
            '/{{(.*)}}/miU',
            function ($match) use ($locationData) {
                $renderer = $this->variableRenderers[$match['1']] ?? null;

                if ($renderer instanceof VariablesRendererInterface) {
                    return $renderer->renderVariable($this->location, $match['1']);
                }

                if (isset($locationData[$match['1']]) || isset($locationData['attributes'][$match['1']])) {
                    if (isset($locationData['attributes'][$match['1']])) {
                        return $this->convertAttributeData($locationData['attributes'][$match['1']]);
                    }

                    return $this->getPreparedValue($match['1']);
                } else {
                    return '';
                }
            },
            $template
        );

        return $html;
    }

    /**
     * Get prepared value by key
     *
     * @param string $key
     * @return string
     */
    private function getPreparedValue($key)
    {
        switch ($key) {
            case 'name':
                if ($this->location->getUrlKey() && $this->configProvider->getEnablePages()) {
                    return '<div class="amlocator-title"><a class="amlocator-link" href="' . $this->getLocationUrl()
                        . '" title="' . $this->escaper->escapeHtml($this->location->getData($key))
                        . '" target="_blank">'
                        . $this->escaper->escapeHtml($this->location->getData($key)) . '</a></div>';
                }

                return '<div class="amlocator-title">' . $this->escaper->escapeHtml($this->location->getData($key))
                    . '</div>';
            case 'description':
            case 'short_description':
                return $this->getPreparedDescription($key);
            case 'country':
                return $this->escaper->escapeHtml($this->getCountryName());
            case 'state':
                return $this->escaper->escapeHtml($this->getStateName());
            case 'rating':
                return $this->location->getData($key);
            case 'photo':
                $photo = $this->location->getData($key);

                return '<div class="amlocator-image"><img src="' . $this->escaper->escapeUrl($photo) . '"></div>';
            default:
                return $this->escaper->escapeHtml($this->location->getData($key));
        }
    }

    /**
     * Get prepared description
     *
     * @return string
     */
    public function getPreparedDescription($key)
    {
        $descriptionLimit = $this->configProvider->getDescriptionLimit();
        $description = $this->filterProvider->getPageFilter()->filter($this->location->getData($key));
        $description = strip_tags(
            preg_replace('#(<style.*?>).*?(</style>)#', '$1$2', $description)
        );
        if (strlen($description) < $descriptionLimit) {
            return '<div class="amlocator-description">' . $description . '</div>';
        }

        if ($descriptionLimit) {
            if (preg_match('/^(.{' . ($descriptionLimit) . '}.*?)\b/isu', $description, $matches)) {
                $description = $matches[1] . '...';
            }

            if ($this->configProvider->getEnablePages()) {
                $description .= '<a href="' . $this->getLocationUrl() . '" title="read more" target="_blank"> '
                    . __('Read More') . '</a>';
            }
        }

        return '<div class="amlocator-description">' . $description . '</div>';
    }

    /**
     * Convert attributes data to html
     *
     * @param array $attributeData
     *
     * @return string $html
     */
    private function convertAttributeData($attributeData)
    {
        $html = $this->escaper->escapeHtml($attributeData['frontend_label']) . ':<br>';
        if (isset($attributeData['option_title']) && is_array($attributeData['option_title'])) {
            foreach ($attributeData['option_title'] as $option) {
                $html .= '- ' . $this->escaper->escapeHtml($option) . '<br>';
            }
            return $html;
        } else {
            $value = isset($attributeData['option_title']) ? $attributeData['option_title'] : $attributeData['value'];

            return $html . $this->escaper->escapeHtml($value) . '<br>';
        }
    }

    /**
     * Get country name
     *
     * @return string
     */
    private function getCountryName()
    {
        return $this->countryFactory->create()->loadByCode($this->location->getCountry())->getName();
    }

    /**
     * Get state name
     *
     * @return string
     */
    private function getStateName()
    {
        $stateName = $this->regionFactory->create()->load($this->location->getState())->getName();

        return $stateName ? $stateName : $this->location->getState();
    }

    /**
     * Get location url
     *
     * @return string
     */
    private function getLocationUrl()
    {
        return $this->escaper->escapeUrl(
            $this->urlBuilder->getUrl($this->configProvider->getUrl() . '/' . $this->location->getUrlKey())
        );
    }
}
