<?php



/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Console\Command\License;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\Inputoption;

/**
 * Class Activate
 * @package Wyomind\Framework\Console\Command\License
 */
class Activate extends \Wyomind\Framework\Console\Command\LicenseAbstract
{

    /**
     *
     */
    public function configure()
    {
        $this->setName('wyomind:license:activate')
            ->setDescription(__('Activate the license for an Wyomind module'))
            ->setDefinition([
                new InputArgument(
                    "module",
                    InputArgument::REQUIRED,
                    __('The module for which you want to activate the license (eg: Wyomind_Framework)')
                ),
                new InputArgument(
                    "activation-key",
                    InputArgument::OPTIONAL,
                    __('The activation key to use to activate the license')
                ),

                new Inputoption(
                    "auto-request",
                    "r",
                    Inputoption::VALUE_NONE,
                    __('Automatically send a license request')
                )
            ]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
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
        $autoRequest = $input->getOption("auto-request");

        if ($module === "all") {
            foreach ($list as $info) {
                $this->activate($info["name"], $input, $output);
            }
        } else {
            $found = false;
            foreach ($list as $info) {
                if ($module === $info["name"]) {
                    $found = true;
                    break;
                }
            }


            if (!$found) {
                $message = __("The module %1 cannot be found", $module);
                $message .= "\n" . __("Available modules are:");
                foreach ($list as $info) {
                    $message .= "\n  - " . $info['name'];
                }
                throw new \Exception($message);
            }
            if (empty($ak)) {
                throw new \Exception(__("The activation key cannot be empty"));
            }

            $this->activate($module, $input, $output, $ak, $autoRequest);
        }
        return $returnValue;
    }
}
