<?php

namespace App\Jobs;

use Carbon\CarbonInterface;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;


class ImportCsv extends BaseImportCsv
{
    public $tries = 1;

    public function retryUntil(): ?CarbonInterface
    {
        return null;
    }
}
