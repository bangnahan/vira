<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'event_code',
        'activity_type',
        'submission_mode',
        'race_type',
        'description',
        'rules_and_terms',
        'registration_start',
        'registration_end',
        'race_start',
        'race_end',
        'banner_image',
        'meta_pixel_id',
        'meta_capi_token',
        'meta_test_code',
        'is_meta_capi_enabled',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'registration_start' => 'datetime',
            'registration_end' => 'datetime',
            'race_start' => 'datetime',
            'race_end' => 'datetime',
            'is_meta_capi_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function templateDesigns(): HasMany
    {
        return $this->hasMany(TemplateDesign::class);
    }

    public function bibTemplate()
    {
        return $this->hasOne(TemplateDesign::class)->where('type', 'BIB');
    }

    public function certificateTemplate()
    {
        return $this->hasOne(TemplateDesign::class)->where('type', 'CERTIFICATE');
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function isRegistrationOpen(): bool
    {
        $now = now();

        return $this->is_active && $now->between($this->registration_start, $this->registration_end);
    }

    public function isRacePeriodActive(): bool
    {
        $now = now();

        return $now->between($this->race_start, $this->race_end);
    }

    /**
     * Resolusi URL gambar banner hero event yang valid untuk frontend.
     */
    public function getBannerUrlAttribute(): string
    {
        if (empty($this->banner_image) || rtrim($this->banner_image, '/') === '/storage' || $this->banner_image === '/storage/') {
            return 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1200&q=80';
        }

        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        if (str_starts_with($this->banner_image, '/storage/')) {
            return $this->banner_image;
        }

        return '/storage/'.ltrim($this->banner_image, '/');
    }
}
