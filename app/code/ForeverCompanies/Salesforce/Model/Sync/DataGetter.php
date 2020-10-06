<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;


class DataGetter
{
    /**
     * @var string
     */
    protected $job;

    public function __construct(
        Job $job
    ){
        $this->_job = $job;
    }
}
