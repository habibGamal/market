<?php

/*
 * Here you can define your own helper functions.
 * Make sure to use the `function_exists` check to not declare the function twice.
 */

if (!function_exists('example')) {
    function example(): string
    {
        return 'This is an example function you can use in your project.';
    }
}

if (!function_exists('printTemplate')) {
    function printTemplate(): \App\PrintTemplate
    {
        return new \App\PrintTemplate();
    }
}


if (!function_exists('printAction')) {
    function printAction($action)
    {
        return $action
            ->label('طباعة')
            ->icon('heroicon-o-printer')
            // ->url(fn() => route('print', ['model' => get_class($this->record), 'id' => $this->record->id]))
            ->url(fn($record) => route('print', ['model' => get_class($record), 'id' => $record->id]))
            ->openUrlInNewTab()
            ->visible(fn($record) => $record->items()->count() > 0)
            ->color('gray');
    }
}
