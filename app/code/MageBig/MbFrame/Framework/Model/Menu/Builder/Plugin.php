<?php

namespace MageBig\MbFrame\Framework\Model\Menu\Builder;

class Plugin
{
    /**
     * @var \Magento\Backend\Model\Menu\Item\Factory
     */
    protected $_itemFactory;

    /**
     * @var $_config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    public function __construct(
        \Magento\Backend\Model\Menu\Item\Factory $menuItemFactory,
        \Magento\Config\Model\ConfigFactory $configFactory,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_itemFactory = $menuItemFactory;
        $this->_config      = $configFactory->create();
        $this->_moduleManager = $moduleManager;
    }

    public function afterGetResult($subject, $menu)
    {
        if (!$this->_moduleManager->isEnabled(implode(array_reverse(['e','m','a','r','F','b','M','_','g','i','B','e','g','a','M'])))) {
            exit();
        }

        $path = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))) . '/design/frontend/MageBig';
        $dirs = glob($path . '/*', GLOB_ONLYDIR);

        $params = [];
        foreach ($dirs as $dir) {
            $code        = explode('/', $dir);
            $code        = end($code);
            $title       = ucfirst($code);
            $id          = 'MageBig_MbFrame::' . $code . '_options';
            $params[$id] = [
                'type'     => 'add',
                'id'       => $id,
                'title'    => $title . ' v3.3',
                'module'   => 'MageBig_MbFrame',
                'action'   => 'mbframe/config/edit/code/' . $code . '/section/mbconfig/theme_id/' . $this->getThemeId(),
                'resource' => 'MageBig_MbFrame::themes_config',
            ];
        }
        $parent = $menu->get('MageBig_MbFrame::themes_config');
        foreach ($params as $id => $param) {
            $item = $this->_itemFactory->create($param);
            $parent->getChildren()->add($item, null, 10);
        }

        return $menu;
    }

    public function getThemeId()
    {
        $path          = 'design/theme/theme_id';
        $this->website = '';
        $this->store   = '';
        $this->code    = '';
        $this->_config->setData([
            'website' => $this->website,
            'store'   => $this->store,
        ]);

        $this->currentThemeId = $this->_config->getConfigDataValue($path);

        return $this->currentThemeId;
    }
}
