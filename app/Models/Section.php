<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Enums\SectionLocation;
use App\Enums\SectionType;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Section extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'active',
        'sort_order',
        'business_type_id',
        'location',
        'section_type'
    ];

    protected $casts = [
        'active' => 'boolean',
        'location' => SectionLocation::class,
        'section_type' => SectionType::class
    ];

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'sectionable');
    }

    public function brands(): MorphToMany
    {
        return $this->morphedByMany(Brand::class, 'sectionable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'sectionable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('section')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " القسم");
    }
}
