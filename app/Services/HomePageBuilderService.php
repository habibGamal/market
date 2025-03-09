<?php

namespace App\Services;

use App\Models\BusinessType;
use App\Models\Section;
use App\Models\Product;
use App\Models\OrderItem;
use App\Enums\SectionLocation;
use App\Enums\SectionType;
use App\Enums\VirturalSectionNames;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HomePageBuilderService
{

    public function __construct(
        protected SectionService $sectionService
    ) {
    }

    public function getHomePageContent(BusinessType $businessType): array
    {
        $sectionQuery = $this->getHomeSections($businessType);

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
            'sliders' => $this->getActiveSliders($businessType),
            'announcements' => $this->getActiveAnnouncements($businessType),
            'sections' => inertia()->merge(
                $sectionsPagination->items()
            ),
            'pagination' => Arr::except($sectionsPagination->toArray(), ['data']),
            ...$paginators,
        ];
    }

    protected function getActiveSliders(BusinessType $businessType): Collection
    {
        return $businessType->sliders()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    protected function getActiveAnnouncements(BusinessType $businessType): Collection
    {
        return $businessType->announcements()
            ->where('active', true)
            ->get();
    }

    protected function getHomeSections(BusinessType $businessType)
    {
        $sectionsQuery = $businessType->sections()
            ->where('location', SectionLocation::HOME)
            ->where('active', true)
            ->orderBy('sort_order');

        return $sectionsQuery;
    }


}
