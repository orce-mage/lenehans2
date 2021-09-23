<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Console\Command\License;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Class Request
 * @package Wyomind\Framework\Console\Command\License
 */
class Request extends \Wyomind\Framework\Console\Command\LicenseAbstract
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('wyomind:license:request')
            ->setDescription(__('Request an additional license for the Wyomind modules'))
            ->setDefinition([
                new InputArgument(
                    "module",
                    InputArgument::REQUIRED,
                    __('The module for which you want to request the new license (eg: Wyomind_Framework)')
                ),
                new InputArgument(
                    "activation-key",
                    InputArgument::REQUIRED,
                    __('The activation key to use to activate the license')
                )
            ]);
        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->create();
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }


        $list = $this->license->getModuleList();
        $module = $input->getArgument("module");
        $ak = $input->getArgument("activation-key");

        $found = false;
        foreach ($list as $info) {
            if ($module === $info["name"]) {
                $found = true;
                break;
            }
        }

        if (empty($ak)) {
            throw new \Exception(__("The activation key cannot be empty"));
        }
        if (!$found) {
            $message = __("The module %1 cannot be found", $module);
            $message .= "\n" . __("Available modules are:");
            foreach ($list as $info) {
                $message .= "\n  - " . $info['name'];
            }
            throw new \Exception($message);
        }

        $this->request($module, $output, $ak);
        return $returnValue;


    }
}
