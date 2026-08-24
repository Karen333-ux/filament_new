<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('is_published')
                            ->label('Published'),
                    ]),

                Section::make('Media')
                    ->schema([
                        // The collection names match Course::registerMediaCollections().
                        // Media library owns the files, so nothing is stored on the
                        // courses table itself.
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection('cover')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5 * 1024)
                            ->helperText('JPEG, PNG or WebP, up to 5 MB. Replaces any existing cover.'),

                        SpatieMediaLibraryFileUpload::make('materials')
                            ->collection('materials')
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            // php-fpm accepts 100 MB per upload; staying under it
                            // means a rejected file fails in the form with a clear
                            // message rather than as a 413 from nginx.
                            ->maxSize(50 * 1024)
                            ->helperText('Slides, PDFs, anything the course needs. Up to 50 MB each.'),
                    ]),
            ]);
    }
}
