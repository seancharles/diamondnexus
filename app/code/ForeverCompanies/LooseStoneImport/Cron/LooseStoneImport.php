<?php

namespace ForeverCompanies\LooseStoneImport\Cron;

use ForeverCompanies\LooseStoneImport\Model\StoneImport;

class BuildFeedsLate
{
    protected $stoneImportModel;

    public function __construct(
        StoneImport $stone
    ) {
        $this->stoneImportModel = $stone;
    }

    public function execute() 
    {
        $this->stoneImportModel->run();
        return;
    }
}