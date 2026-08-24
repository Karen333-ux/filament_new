<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role')
                    ->schema([
                        TextInput::make('name')
                            ->label('Role name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                ...static::permissionSections(),
            ]);
    }

    /**
     * One collapsible section per group in config/permissions.php. Adding a
     * group or a permission there is the only edit needed for it to appear on
     * this page — nothing in this class enumerates them.
     *
     * The lists are grouped for readability only; CreateRole and EditRole
     * flatten the nested state back into the role's single permission list.
     *
     * @return array<int, Section>
     */
    protected static function permissionSections(): array
    {
        return collect(config('permissions.groups'))
            ->map(fn (array $permissions, string $group): Section => Section::make($group)
                ->schema([
                    CheckboxList::make('permissions.'.Str::slug($group))
                        ->hiddenLabel()
                        ->options($permissions)
                        ->columns(2)
                        ->bulkToggleable(),
                ])
                ->collapsible())
            ->values()
            ->all();
    }
}
