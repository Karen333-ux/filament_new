<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Services\CourseStats;
use App\Support\Cache\CacheLayer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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

    /**
     * Read through the cache layer. The payload carries the timestamp of the
     * moment it was computed, which the view prints — so a visitor can see the
     * figures being served from cache rather than take it on trust.
     */
    public function stats(): array
    {
        return app(CourseStats::class)->get();
    }

    public function cacheKey(): string
    {
        return app(CacheLayer::class)->key(CourseStats::GROUP, CourseStats::KEY);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rebuildCache')
                ->label('Rebuild cache')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    // Invalidate then warm, rather than only warming: bumping
                    // the version is what retires anything else cached under
                    // this group, not just the one entry warm() rewrites.
                    app(CacheLayer::class)->invalidate(CourseStats::GROUP);
                    app(CourseStats::class)->warm();

                    Notification::make()
                        ->title('Cache rebuilt')
                        ->body('The course figures were recomputed from the database.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
