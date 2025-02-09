<?php

namespace App\Services;

use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class InvoiceItemsCSVService
{
    /**
     * Export invoice items to CSV file.
     *
     * @param \Illuminate\Support\Collection $items
     * @param array $titles
     * @param callable $mapperCallback
     * @return \League\Csv\Writer
     */
    public function export($items,$titles,$mapperCallback):Writer
    {
        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Set UTF-8 BOM for proper Arabic character encoding
        $csv->setOutputBOM(Writer::BOM_UTF8);

        // Add headers
        $csv->insertOne($titles);

        // Add records
        $records = $items->map($mapperCallback)->toArray();

        $csv->insertAll($records);

        return $csv;
    }

    /**
     * Import CSV file.
     *
     * @param string $path
     * @return \Illuminate\Support\Collection
     */
    public function import($path){
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(',');
        $records = collect($csv->getRecords());
        return $records;
    }
}
