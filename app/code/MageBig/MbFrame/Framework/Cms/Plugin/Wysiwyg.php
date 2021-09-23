<?php

namespace MageBig\MbFrame\Framework\Cms\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;

class Wysiwyg
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    protected $activeEditor;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Wysiwyg constructor.
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param null $activeEditor
     * @param RequestInterface|null $request
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        $activeEditor = null,
        RequestInterface $request = null
    ) {
        $this->assetRepo = $assetRepo;
        try {
            /* Fix for Magento 2.1.x & 2.2.x that does not have this class and plugin should not work there */
            if (class_exists(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class)) {
                $this->activeEditor = $activeEditor
                    ?: ObjectManager::getInstance()->get(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class);
            }
        } catch (\Exception $e) {
        }

        $this->request = $request ?: ObjectManager::getInstance()->get(\Magento\Framework\App\RequestInterface::class);
    }

    /**
     * Enable variables & widgets on product edit page
     *
     * @param \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface
     * @param array $data
     * @return array
     */
    public function beforeGetConfig(
        \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface,
        $data = []
    ) {
        if (!$this->activeEditor) {
            return [$data];
        }

        $data['add_variables'] = true;
        $data['add_widgets'] = true;

        return [$data];
    }

    /**
     * Return WYSIWYG configuration
     *
     * @param \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface
     * @param \Magento\Framework\DataObject $result
     * @return \Magento\Framework\DataObject
     */
    public function afterGetConfig(
        \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface,
        \Magento\Framework\DataObject $result
    ) {
        if (!$this->activeEditor) {
            return $result;
        }

        // Get current wysiwyg adapter's path
        $editor = $this->activeEditor->getWysiwygAdapterPath();

        // Is the current wysiwyg tinymce v4?
        if (strpos($editor, 'tinymce4Adapter')) {
            $plugins = $result->getData('plugins');

            if (isset($plugins[0]) && $plugins[0]['name'] == 'image') {
                $plugins[0]['src'] = $this->assetRepo->getUrl('MageBig_MbFrame::js/tiny_mce_4/plugins/image/plugin.min.js');
                $plugins[] = [
                    'name' => 'imagetools',
                    'src' => $this->assetRepo->getUrl('MageBig_MbFrame::js/tiny_mce_4/plugins/imagetools/plugin.min.js')
                ];
                $result->setData('plugins', $plugins);

                $settings = $result->getData('settings');
                $mainCss = $result->getData('tinymce4')['content_css'];
                if (!is_array($mainCss)) {
                    $mainCss = explode(',', $mainCss);
                }
                $mainCss[] = $this->assetRepo->getUrl('MageBig_MbFrame::css/tiny_mce/content.min.css');
                $settings['content_css'] = $mainCss;

                $result->setData('settings', $settings);
            }

            return $result;
        } else {
            // don't make any changes if the current wysiwyg editor is not tinymce 4
            return $result;
        }
    }
}
