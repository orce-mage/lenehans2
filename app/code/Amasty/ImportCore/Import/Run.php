<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import;

use Amasty\ImportCore\Api\Config\ProfileConfigInterface;

class Run
{
    /**
     * @var ImportStrategy[]
     */
    private $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    public function execute(ProfileConfigInterface $profileConfig, string $processIdentity)
    {
        $strategy = $profileConfig->getStrategy();
        if (empty($this->strategies[$strategy])) {
            throw new \LogicException('Strategy "' . $strategy . '" does not exist');
        }

        return $this->strategies[$strategy]->run($profileConfig, $processIdentity);
    }
}
