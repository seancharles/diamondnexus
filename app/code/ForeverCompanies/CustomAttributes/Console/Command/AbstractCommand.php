<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @var State
     */
    protected $state;

    protected $name = '';

    /**
     * TransformAttributes constructor.
     * @param State $state
     * @param TransformData $helper
     */
    public function __construct(
        State $state,
        TransformData $helper
    ) {
        $this->state = $state;
        $this->helper = $helper;
        parent::__construct($this->name);
    }
}
