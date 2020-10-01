<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Console\Command;

use ForeverCompanies\Promotions\Helper\Data;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var State
     */
    protected $state;

    protected $name = '';

    /**
     * Promotions constructor.
     * @param State $state
     * @param Data $helper
     */
    public function __construct(
        State $state,
        Data $helper
    ) {
        $this->state = $state;
        $this->helper = $helper;
        parent::__construct($this->name);
    }
}
