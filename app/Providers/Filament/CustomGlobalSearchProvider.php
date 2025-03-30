<?php

namespace App\Providers\Filament;

use Filament\GlobalSearch\Contracts\GlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

class CustomGlobalSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $builder = GlobalSearchResults::make();

        // foreach (Filament::getResources() as $resource) {
        //     if (! $resource::canGloballySearch()) {
        //         continue;
        //     }

        //     $resourceResults = $resource::getGlobalSearchResults($query);

        //     if (! $resourceResults->count()) {
        //         continue;
        //     }

        //     $builder->category($resource::getPluralModelLabel(), $resourceResults);
        // }

        $searchQuery = Str::lower($query);
        foreach (Filament::getResources() as $resource) {

            $pluralModelLabel = Str::lower($resource::getPluralModelLabel());

            // Skip resources whose plural model label doesn't match the search query
            if (!Str::contains($pluralModelLabel, $searchQuery)) {
                continue;
            }

            // Create a result for the resource itself pointing to its index page
            $result = new GlobalSearchResult(
                title: $resource::getPluralModelLabel(),
                url: $resource::getUrl(),
                // details: ['صفحة الفهرس'], // "Index page" in Arabic
            );

            $builder->category($resource::getPluralModelLabel(), [$result]); // "Resources" in Arabic
        }

        return $builder;
    }
}
