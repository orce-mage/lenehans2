<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Exception;

/**
 * Occurs when parent process delegates all remaining group actions to child process
 */
class JobDelegatedException extends \RuntimeException
{
}
