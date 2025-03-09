<?php

namespace App\Observers;

use App\Enums\VirturalSectionNames;
use App\Models\BusinessType;
use App\Models\Section;
use App\Enums\SectionLocation;
use App\Enums\SectionType;

class BusinessTypeObserver
{
    /**
     * Handle the BusinessType "created" event.
     */
    public function created(BusinessType $businessType): void
    {
        // Create Most Trendy section
        Section::create([
            'title' => VirturalSectionNames::TREND,
            'business_type_id' => $businessType->id,
            'section_type' => SectionType::VIRTUAL->value,
            'location' => SectionLocation::HOME->value,
            'active' => true,
            'sort_order' => 1
        ]);

        // Create Customer Recommendations section
        Section::create([
            'title' => VirturalSectionNames::RECOMMENDATION,
            'business_type_id' => $businessType->id,
            'section_type' => SectionType::VIRTUAL->value,
            'location' => SectionLocation::HOME->value,
            'active' => true,
            'sort_order' => 2
        ]);
    }
}
