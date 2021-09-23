<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Console\Command;

/**
 * $ bin/magento help wyomind:massproductimport:run
 * Usage:
 * wyomind:massproductimport:run [profile_id1] ... [profile_idN]
 *
 * Arguments:
 * profile_id            Space-separated list of import profiles (run all profiles if empty)
 *
 * Options:
 * --help (-h)           Display this help message
 * --quiet (-q)          Do not output any message
 * --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * --version (-V)        Display this application version
 * --ansi                Force ANSI output
 * --no-ansi             Disable ANSI output
 * --no-interaction (-n) Do not ask any interactive question
 */
class Run extends \Wyomind\MassStockUpdate\Console\Command\Run
{
    public $module = "MassProductImport";
    public $name = "Mass Product Import & Update";
}
