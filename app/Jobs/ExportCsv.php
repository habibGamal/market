<?php

namespace App\Jobs;

use Carbon\CarbonInterface;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Exports\Jobs\ExportCsv as BaseExportCsv;


class ExportCsv extends BaseExportCsv
{
    public $tries = 1;

    public function retryUntil(): ?CarbonInterface
    {
        return null;
    }
}
