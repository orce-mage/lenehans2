<?php

namespace Wyomind\Framework\Plugin\Backend\Model\Menu;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;
use Wyomind\Framework\Helper\Module;

class Builder
{
    /**
     * @var Module
     */
    protected $module;
    /**
     * @var ItemFactory
     */
    private $menuItemFactory;

    /**
     * BuilderPlugin constructor.
     * @param ItemFactory $menuItemFactory
     * @param Module $module
     */
    public function __construct(
        ItemFactory $menuItemFactory,
        Module $module
    ) {
    
        $this->menuItemFactory = $menuItemFactory;
        $this->module = $module;
    }

    public function getOrphan(&$results, $children, $itemId)
    {
        $result = null;
        /** @var Item $item */
        foreach ($children as $item) {
            if (strpos($item->getId(), $itemId) === 0
                && strpos($item->getId(), "_copy") === false
                && strpos($item->getId(), "fwk_") === false
                && $item->getAction() != "") {
                $results[] = $item->getId();
                break;
            }
            if ($item->hasChildren() && ($result = $this->getOrphan($results, $item->getChildren(), $itemId))) {
                break;
            }
        }
        return $result;
    }

    public function copy($menu, $idFrom, $idTo, $idParent)
    {
        if ($menu->get($idTo)) {
            return;
        }
        $itemFrom = $menu->get($idFrom);
        $itemData = $itemFrom->toArray();

        $itemData['id'] = $idTo;
        $subMenu = $itemData['sub_menu'] ?? [];

        foreach ($subMenu as $k => &$sub) {
            $sub['id'] .= '_copy';
            if ($menu->get($sub['id'])) {
                unset($subMenu[$k]);
            }
        }
        $itemData['sub_menu'] = $subMenu;

        $item = $this->menuItemFactory->create([
            'data' => $itemData
        ]);
        $menu->add($item, $idParent, $itemData['sort_index']);
    }

    /**
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     */
    public function afterGetResult($subject, Menu $menu)
    {

        $newMenu = clone $menu;
        $installedModules = [];

        $copy = $menu->getArrayCopy();

        if ($main = $menu->get('Wyomind_Framework::main')) {
            $allExtensions = $this->module->getAllExtensions();
            $modules = $this->module->getModuleListAll(true);

            foreach ($modules as $module) {
                if (!$module['label']) {
                    continue;
                }
                $moduleName = $module['name'];
                $installedModules[] = strtoupper($module['code']);


                $copyMenu = null;
                $parent = 'Wyomind_Framework::main';


                // create main menu in Framework Menu
//                if (!$moduleMenu) {
                if ($module['label']) {
                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'parent_id' => $parent,
                            'id' => $moduleName . '::fwk_main',
                            'title' => $module['label'],
                            'resource' => $moduleName . '::fwk_main'
                        ]
                    ]);
                    $newMenu->add($item, $parent);
                    $copyMenu = $item;
                }


                // Original module menu
                $moduleMenu = $menu->get($moduleName . '::main');
                if (!$moduleMenu) {
                    $moduleMenu = $menu->get($moduleName . '::menu');
                }
                if (!$moduleMenu) {
                    $moduleMenu = $menu->get($moduleName . '::global');
                }
                
                $prefix = $this->module->getPrefix($moduleName);

                // if original menu exists
                if ($moduleMenu and $moduleMenu->getAction() == "") {
                    $this->copy($newMenu, $moduleMenu->getId(), $moduleMenu->getId() . '_copy', $copyMenu->getId());
                    $menuForOrphans = $moduleMenu->getId() . '_copy';
                } else { // no original menu
                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'parent_id' => $parent,
                            'id' => $moduleName . '::main_copy',
                            'title' => $module['label'],
                            'resource' => $moduleName . '::main_copy'
                        ]
                    ]);
                    $newMenu->add($item, $copyMenu->getId());
                    $menuForOrphans = $item->getId();
                }

                // adding module menu children
                $results = [];
                $this->getOrphan($results, $copy, $moduleName);

                foreach ($results as $entry) {
                    if (!$newMenu->get($entry . '_copy')) {
                        $this->copy($newMenu, $entry, $entry . '_copy', $menuForOrphans);
                    } elseif (strpos($entry, '::main') !== false) {
                        $this->copy($newMenu, $entry, $entry . '_sub_copy', $menuForOrphans);
                    }
                }


                // module config
                if (!$menu->get($moduleName . '::configuration') && !$menu->get($moduleName . '::config')) {
                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'parent_id' => $menuForOrphans,
                            'id' => $moduleName . '::configuration_generated',
                            'title' => __('Configuration')->getText(),
                            'resource' => $moduleName . '::configuration_generated',
                            'sort_index' => 1,
                            'action' => 'adminhtml/system_config/edit/section/' . $prefix . str_replace('wyomind_', '', strtolower($moduleName))
                        ]
                    ]);
                    $newMenu->add($item, $menuForOrphans, 1);
                }

                // user guide
                if (isset($allExtensions['by_code'][strtoupper($module['code'])])) {
                    /** @var Menu\Item $item */
                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'parent_id' => $menuForOrphans,
                            'id' => $moduleName . '::userguide_' . $module['code'],
                            'title' => __('User Guide')->getText(),
                            'target' => '_blank',
                            'resource' => $moduleName . '::userguide',
                            'action' => 'userguide',
                            'sort_index' => 999999
                        ]
                    ]);

                    $newMenu->add($item, $menuForOrphans, 999999);
                }
            }


            $modules = $main->getChildren();
            foreach ($modules as $moduleName) {
                $entries = $moduleName->getChildren();
                foreach ($entries as $entry) {
                    // assigning a sort_order for entry not having one
                    $subEntries = $entry->getChildren();
                    foreach ($subEntries as $subEntry) {
                        if ($subEntry->toArray()['sort_index'] == null) {
                            $data = $subEntry->toArray();
                            $data['sort_index'] = 2;
                            $subEntry->populateFromArray($data);
                        }
                        //$subEntry->setTitle($subEntry->getTitle() . ' [' . $subEntry->toArray()['sort_index'] . ']');
                    }

                    // reodering module entries
                    $backup = [];
                    foreach ($entry->toArray()['sub_menu'] as $key => $subEntry) {
                        $newMenu->remove($subEntry['id']);
                        $subEntry['parent_id'] = $entry->getId();
                        $subEntry['id'] .= "_sorted";
                        $backup[] = $subEntry;
                    }
                    uasort($backup, function ($a, $b) {
                        return $a['sort_index'] < $b['sort_index'];
                    });
                    foreach ($backup as $newEntry) {
                        $item = $this->menuItemFactory->create(['data' => $newEntry]);
                        $newMenu->add($item, $newEntry['parent_id'], $newEntry['sort_index']);
                    }
                }
            }

            if ($discover = $menu->get('Wyomind_Framework::discover')) {
                $position = 0;

                $keys = array_keys($allExtensions);
                shuffle($keys);
                $random = [];
                foreach ($keys as $key) {
                    $random[$key] = $allExtensions[$key];
                }

                foreach ($random as $groupTitle => $modules) {
                    if ($groupTitle == "by_code") {
                        continue;
                    }
                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'parent_id' => $discover->getId(),
                            'id' => 'Wyomind_Framework::discover_group_' . $position,
                            'title' => $groupTitle,
                            'resource' => 'Wyomind_Framework::discover_group_' . $position,
                            'sort_index' => $position
                        ]
                    ]);
                    $newMenu->add($item, $discover->getId(), $position);
                    $subPosition = 0;
                    foreach ($modules as $code => $data) {
                        if (!in_array($code, $installedModules) && isset($allExtensions['by_code'][$code])) {
                            $item = $this->menuItemFactory->create([
                                'data' => [
                                    'parent_id' => 'Wyomind_Framework::discover_group_' . $position,
                                    'id' => 'Wyomind_Framework::discover_' . $code,
                                    'title' => $data['name'],
                                    'resource' => 'Wyomind_Framework::discover_' . $code,
                                    'sort_index' => $subPosition,
                                    'target' => '_blank'

                                ]
                            ]);
                            $newMenu->add($item, 'Wyomind_Framework::discover_group_' . $position, $subPosition);

                            $subPosition++;
                        }
                    }

                    if ($subPosition == 0) { // no extension added for the group
                        $newMenu->remove($item->getId());
                    }

                    $position++;
                }
            }
        }

        return $newMenu;
    }
}
