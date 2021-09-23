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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class InsertCode
 * @package Wyomind\Framework\Console\Command\License
 */
class InsertCode extends \Wyomind\Framework\Console\Command\LicenseAbstract
{


    /**
     *
     */
    protected function configure()
    {
        $this->setName('wyomind:license:insertcode')
            ->setDescription(__('Insert the license code for a Wyomind module'))
            ->setDefinition([
                new InputArgument(
                    "module",
                    InputArgument::REQUIRED,
                    __('The module for which you want to add the license code (eg: Wyomind_Framework)')
                ),
                new InputArgument(
                    "license-code",
                    InputArgument::REQUIRED,
                    __('The license code to insert to activate the license')
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->create();
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }


        $this->config =$this->configFactory->create();

        $list =$this->license->getModuleList();
        $module = $input->getArgument("module");
        $code = $input->getArgument("license-code");


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
        if (empty($code)) {
            throw new \Exception(__("The license codecannot be empty"));
        }

        $this->insert($module, $output, $code);


        return $returnValue;
    }

    /**
     * @param $module
     * @param $output
     * @param bool $code
     * @throws \Exception
     */

    protected function insert($module, & $output, $code = false)
    {


        $ext = strtolower($module);
        $prefix =$this->license->getPrefix($module);


        $ak =$this->license->getDefaultConfigUncrypted($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_key");
        if (empty($ak)) {
            $output->writeln("<error>" . __("Unable to activate: no activation key found for") . " " . $module . "</error>");
            $output->writeln("<comment>" . __("Please run wyomind:license:activate") . " " . $module . " " . "<activation_key>" . "</comment>");

            return;
        }


        try {
            $this->license->setStoreConfigCrypted($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_code", $code);
            $output->writeln("<bg=green;fg=black>" . __("License code inserted for") . " " . $module . "</>");
            return;
        } catch (\RuntimeException $e) {
            throw new \Exception(__("Unable to insert the license code."));
        }

    }
}
