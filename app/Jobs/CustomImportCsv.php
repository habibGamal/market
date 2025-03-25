<?php

namespace App\Jobs;

use Carbon\CarbonInterface;
use Filament\Actions\Imports\Jobs\ImportCsv;


class CustomImportCsv extends ImportCsv
{
    public $tries = 1;

    public function retryUntil(): ?CarbonInterface
    {
        return null;
    }
}
