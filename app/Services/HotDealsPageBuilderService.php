<?php

namespace App\Services;

use App\Models\BusinessType;
use App\Models\Section;
use App\Enums\SectionLocation;
use Illuminate\Support\Arr;

class HotDealsPageBuilderService
{
    public function __construct(
        protected SectionService $sectionService
    ) {
    }

    public function getHotDealsContent(BusinessType $businessType): array
    {
        $sectionQuery = $this->getHotDealsSections($businessType);

        $sectionsPagination = $sectionQuery->clone()->paginate(1);

        $paginators = $sectionQuery->clone()->get()
            ->map(function ($section) {
                $productsPagination = $this->sectionService->getProductsOfSection($section)->paginate(3, pageName: 'section_' . $section->id . '_products_page');
                $paginatorIdentifier = 'section_' . $section->id . '_products_page';
                $productsResult[$paginatorIdentifier . '_data'] = inertia()->merge(
                    $productsPagination->items()
                );
                $productsResult[$paginatorIdentifier . '_pagination'] = Arr::except($productsPagination->toArray(), ['data']);
                return $productsResult;
            })
            ->flatMap(
                fn($paginator) => $paginator
            )->toArray();

        return [
            'sections' => inertia()->merge(
                $sectionsPagination->items()
            ),
            'pagination' => Arr::except($sectionsPagination->toArray(), ['data']),
            ...$paginators,
        ];
    }

    protected function getHotDealsSections(BusinessType $businessType)
    {
        $sectionsQuery = $businessType->sections()
            ->where('location', SectionLocation::HOT_DEALS)
            ->where('active', true)
            ->orderBy('sort_order');

        return $sectionsQuery;
    }
}
