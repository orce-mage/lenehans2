<?php

namespace MageBig\MbFrame\Block\Adminhtml;

class Design extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory
     */
    protected $themeCollection;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Design\Collection
     */
    protected $collectionDesign;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $themeDesign;

    /**
     * @var $website
     */
    protected $website;

    /**
     * @var $store
     */
    protected $store;

    /**
     * @var $code
     */
    protected $code;

    /**
     * @var $currentThemeId
     */
    protected $currentThemeId;

    /**
     * Themes constructor.
     * @param \Magento\Backend\Block\Template\Context                         $context
     * @param \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory $themeCollection
     * @param \Magento\Config\Model\Config                                    $config
     * @param \Magento\Theme\Model\Theme                                      $themeDesign
     * @param array                                                           $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory $themeCollection,
        \Magento\Config\Model\Config $config,
        \Magento\Theme\Model\Theme $themeDesign,
        array $data = []
    ) {
        $this->themeCollection = $themeCollection;
        $this->config = $config;
        $this->themeDesign = $themeDesign;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->website = $this->getRequest()->getParam('website');
        $this->store   = $this->getRequest()->getParam('store');
        $this->code    = $this->getRequest()->getParam('code');

        $this->config->setData([
            'website' => $this->website,
            'store'   => $this->store,
        ]);
    }

    public function getActiveThemeUrl($themeId)
    {
        $params             = [];
        $params['theme_id'] = $themeId;
        $params['code']     = $this->code;
        if ($this->website) {
            $params['website'] = $this->website;
        }
        if ($this->store) {
            $params['store'] = $this->store;
        }

        return $this->getUrl('mbframe/theme/save', $params);
    }

    public function getCustomThemeUrl($themeId)
    {
        $params             = [];
        $params['theme_id'] = $themeId;
        $params['code']     = $this->code;
        $params['section']  = 'mbconfig';

        return $this->getUrl('mbframe/config/edit', $params);
    }

    public function getCurrentThemeId()
    {
        return $this->getRequest()->getParam('theme_id');
    }

    public function getThemes()
    {
        $collection = $this->themeCollection->create();
        $collection->addFieldToFilter('main_table.code', ['like' => '%' . $this->code . '%']);

        return $collection;
    }

    public function getPreviewImageUrl($theme)
    {
        $this->themeDesign->load($theme->getThemeId());

        return $this->themeDesign->getThemeImage()->getPreviewImageUrl();
    }
}
