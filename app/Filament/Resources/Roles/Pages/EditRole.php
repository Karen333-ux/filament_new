<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, string> */
    protected array $selectedPermissions = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $held = $this->record->permissions->pluck('name')->all();

        // Split the role's flat permission list back across the groups the
        // form renders, so each checkbox list is handed only its own options.
        $data['permissions'] = collect(config('permissions.groups'))
            ->mapWithKeys(fn (array $permissions, string $group): array => [
                Str::slug($group) => array_values(
                    array_intersect(array_keys($permissions), $held),
                ),
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedPermissions = Arr::flatten($data['permissions'] ?? []);

        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions($this->selectedPermissions);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
