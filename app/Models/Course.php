<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Course extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        // singleFile() means uploading a new cover replaces the old one instead
        // of stacking, which is what the form's single upload field implies.
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('materials');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(320)
            ->height(180)
            // Queued conversions need a worker running; the container has two,
            // but generating this one inline keeps the table thumbnail from
            // showing a broken image for the first few seconds after upload.
            ->nonQueued();
    }
}
