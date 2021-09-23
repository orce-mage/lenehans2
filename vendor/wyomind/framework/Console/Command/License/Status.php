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

class Status extends \Wyomind\Framework\Console\Command\LicenseAbstract
{

    protected function configure()
    {
        $this->setName('wyomind:license:status')
            ->setDescription(__('Check the status of the licenses for the Wyomind modules'))
            ->setDefinition([]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->create();
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }


        $list = $this->license->getModuleListAll();

        $table = new \Symfony\Component\Console\Helper\Table($output);//$this->getHelperSet()->get('Table');
        $table->setHeaders(["Module", "Version", "Activation key", "Status"]);
        $table->setRows([]);

        foreach ($list as $info) {
            $prefix = $this->license->getPrefix($info['name']);

            $activation_key = $this->license->getStoreConfigUncrypted(strtolower($prefix . str_replace("Wyomind_", "", $info['name'])) . "/license/activation_key");
            $license_code = $this->license->getStoreConfigUncrypted(strtolower($prefix . str_replace("Wyomind_", "", $info['name'])) . "/license/activation_code");

            if ($activation_key != "" && $license_code == "") {
                $status = "<error>invalidated</error>";
            } elseif ($license_code != '') {
                $status = "<fg=black;bg=green>success</>";
            } else {
                $status = "<fg=black;bg=yellow>pending</>";
            }

            $key = ($activation_key != '') ? $activation_key : "---";


            $data = [
                $info["name"],
                $info["setup_version"],
                $key,
                $status

            ];
            $table->addRow($data);
        }
        $output->writeln("");
        $table->render($output);
        $output->writeln("");

        return $returnValue;
    }
}
