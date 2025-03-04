<?php

namespace App\Services;

use App\Models\BusinessType;
use App\Models\Section;
use App\Enums\SectionLocation;
use Illuminate\Database\Eloquent\Collection;

class HomePageBuilderService
{
    public function getHomePageContent(BusinessType $businessType): array
    {
        return [
            'sliders' => $this->getActiveSliders($businessType),
            'announcements' => $this->getActiveAnnouncements($businessType),
            'sections' => $this->getHomeSections($businessType)
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

    protected function getHomeSections(BusinessType $businessType): Collection
    {
        return $businessType->sections()
            ->where('location', SectionLocation::HOME)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
