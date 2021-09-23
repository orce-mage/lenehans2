<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Service;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngineInterface;
use Mirasvit\CacheWarmer\Api\Service\BlockMarkServiceInterface;

class BlockMarkService implements BlockMarkServiceInterface, TemplateEngineInterface
{
    /**
     * @var BlockTagsGeneratorService
     */
    private $blockTagsGeneratorService;
    /**
     * @var FullModuleList
     */
    private $fullModuleList;
    /**
     * @var TemplateEngineInterface
     */
    private $subject;
    /**
     * @var array
     */
    private $templates;

    /**
     * @param TemplateEngineInterface $subject
     * @param array $templates
     * @param BlockTagsGeneratorService $blockTagsGeneratorService ,
     * @param FullModuleList $fullModuleList
     */
    public function __construct(
        TemplateEngineInterface $subject,
        $templates,
        $blockTagsGeneratorService,
        FullModuleList $fullModuleList
    ) {
        $this->subject                   = $subject;
        $this->templates                 = $templates;
        $this->blockTagsGeneratorService = $blockTagsGeneratorService;
        $this->fullModuleList            = $fullModuleList;
    }

    /**
     * Mark rendered blocks
     * {@inheritdoc}
     */
    public function render(BlockInterface $block, $templateFile, array $dictionary = [])
    {
        $result = false;
        foreach ($this->templates as $template) {
            $templateAdminPath = $template['template'];
            $isTemplateUsed    = $this->isTemplateApplied($block, $template, $templateFile);
            if ($isTemplateUsed) {
                $blockType  = $block->getType();
                $moduleName = $block->getModuleName();
                $markParams = [
                    BlockMarkServiceInterface::BLOCK_CLASS          => $blockType,
                    BlockMarkServiceInterface::MODULE_NAME          => $moduleName,
                    BlockMarkServiceInterface::TEMPLATE_FILE        => $templateFile,
                    BlockMarkServiceInterface::TEMPLATE_ADMIN_PATH  => $templateAdminPath,
                    BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE
                                                                    => $this->getCmsBlockIdWidgetCode($moduleName, $block, $template),
                    BlockMarkServiceInterface::TEMPLATE_BLOCK_CLASS => $this->getTemplateBlockClass($templateAdminPath),
                ];
                $result     = $this->subject->render($block, $templateFile, $dictionary);
                $result     = $this->markBlocks($markParams, $result);
            }
        }

        if (!$result) {
            $result = $this->subject->render($block, $templateFile, $dictionary);
        }

        return $result;
    }

    /**
     * @param BlockInterface $block
     * @param array          $template
     * @param string         $templateFile
     * @return bool
     */
    protected function isTemplateApplied($block, $template, $templateFile)
    {
        $isTemplateUsed = (strpos($templateFile, $template['template']) !== false) ? true : false;

        if ($isTemplateUsed
            && ($block->getModuleName() == 'Magento_Cms')
            && isset($template['cms_block_id'])
            && ($template['cms_block_id'] != $block->getBlockId())) {
            $isTemplateUsed = false;
        }

        return $isTemplateUsed;
    }

    /**
     * @param string $moduleName
     * @param object $block
     * @param array $template
     * @return bool
     */
    private function getCmsBlockIdWidgetCode($moduleName, $block, $template)
    {
        $cmsBlockIdWidgetCode = false;
        if ($moduleName == 'Magento_Cms') {
            $cmsBlockIdWidgetCode = $block->getBlockId();
        } elseif ($template[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE]
            && (strpos(
                $template[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE],
                BlockMarkServiceInterface:: WIDGET_TYPE
            ) !== false)) {
            $cmsBlockIdWidgetCode = $template[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE];
        }

        return $cmsBlockIdWidgetCode;
    }

    /**
     * Check if block in template is different form block class
     * @param string $templateAdminPath
     * @return bool|string
     */
    protected function getTemplateBlockClass($templateAdminPath)
    {
        $templateBlockClass   = false;
        $fullModuleList       = array_keys($this->fullModuleList->getAll());
        $templatePreparedPath = str_replace('/', '_', $templateAdminPath);
        foreach ($fullModuleList as $module) {
            if (stripos($templatePreparedPath, $module) !== false) {
                $templateBlockClass = $module;
                break;
            }
        }

        return $templateBlockClass;
    }

    /**
     * {@inheritdoc}
     */
    public function markBlocks($markParams, $result)
    {
        return $this->blockTagsGeneratorService->getStartReplacerTag($markParams)
            . $result . $this->blockTagsGeneratorService->getEndReplacerTag($markParams);
    }
}
