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



namespace Mirasvit\CacheWarmer\Plugin;

use Mirasvit\CacheWarmer\Model\Config as ModuleConfig;

class ConfigPlugin
{
    /**
     * @param \Magento\Config\Model\Config $config
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundSave(
        \Magento\Config\Model\Config $config,
        \Closure $proceed
    ) {
        if ($config->getData('section') == 'cache_warmer') {
            $data = $config->getData('groups');

            if (isset($data['general'])) {
                $fieldsData = $data['general']['fields'];

                $isResetState = false;

                if (isset($fieldsData['ignored_uri_expressions'])) {
                    $fieldsData['ignored_uri_expressions']['value'] = $this->prepareExpressions(
                        $fieldsData['ignored_uri_expressions']['value']
                    );

                    $isResetState = true;
                }

                if (isset($fieldsData['ignored_user_agents'])) {
                    $fieldsData['ignored_user_agents']['value'] = $this->prepareExpressions(
                        $fieldsData['ignored_user_agents']['value']
                    );

                    $isResetState = true;
                }

                if (!isset($fieldsData['ignored_page_types'])) {
                    $fieldsData['ignored_page_types']['value'] = null;
                } else {
                    $isResetState = true;
                }

                $data['general']['fields'] = $fieldsData;

                if ($isResetState) {
                    $data['cleanup_pages']['fields']['state']['value'] = 0;
                }
                $config->setData('groups', $data);
            }

            if (isset($data['performance'])) {
                $fieldsData = $data['performance']['fields'];

                switch ($fieldsData['level']['value']) {
                    case ModuleConfig::PERFORMANCE_LOW:
                    case ModuleConfig::PERFORMANCE_MEDIUM:
                    case ModuleConfig::PERFORMANCE_HIGH:
                        $fieldsData['job_schedule']['value'] = '*/2 * * * *';
                        break;
                    default:
                        $fieldsData['job_schedule']['value'] = $fieldsData['job_schedule_custom']['value'];
                        break;
                }

                $data['performance']['fields'] = $fieldsData;
                $config->setData('groups', $data);
            }
        }
        return $proceed();
    }

    /**
     * @param array $field
     * @return array
     */
    private function prepareExpressions($field)
    {
        if (count($field) <= 1) {
            return $field;
        }

        // remove empty expressions
        $filtered = array_filter($field, function ($val) {
            return !(is_array($val) && !$val['expression']);
        });

        foreach ($filtered as $key => $value) {
            if ($key == '__empty') {
                continue;
            }

            $value['expression'] = trim($value['expression'], " \t\n\r\0\x0B");

            // adding correct expression delimiters
            if (!preg_match('/\/i*$/', $value['expression'])) {
                $value['expression'] = $value['expression'] . '/';
            }
            if (!preg_match('/^\//', $value['expression'])) {
                $value['expression'] = '/' . $value['expression'];
            }
            // encapsulate body of expression
            preg_match('/^(\/)(.+)(\/i*)$/', $value['expression'], $matches);

            $exprBody = [];
            $pieces = explode('/', trim($matches[2], " \t\n\r\0\x0B"));

            // escaping / in the expression body
            foreach ($pieces as $idx => $subString) {
                if ($idx < count($pieces) - 1 && strrpos($subString, '\\') !== strlen($subString) - 1) {
                    $subString .= '\\';
                }

                $exprBody[] = $subString;
            }
            $matches[2] = implode('/', $exprBody);

            array_shift($matches);
            $filtered[$key]['expression'] = implode('', $matches);

            set_error_handler(function () {
            }, E_WARNING);
            $isRegularExpression = preg_match($filtered[$key]['expression'], "") !== false;
            restore_error_handler();

            if (!$isRegularExpression) {
                throw new \Exception('Incorrect regular expression ' . $filtered[$key]['expression']);
            }
        }

        return $filtered;
    }
}
