<?php

namespace App\Filament\Pages;

use App\Models\Course;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

class Courses extends Page
{
    protected string $view = 'filament.pages.courses';

    /**
     * CourseResource already owns /admin/courses, and two routes cannot share a
     * slug — this static overview lives beside it rather than replacing it.
     */
    protected static ?string $slug = 'courses-overview';

    protected static ?string $navigationLabel = 'Courses overview';

    protected static ?string $title = 'Courses overview';

    /**
     * A Filament page has no record of its own, so it defers to the same
     * CoursePolicy the resource uses. Returning false hides the navigation item
     * as well as blocking the URL.
     */
    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', Course::class);
    }
}
