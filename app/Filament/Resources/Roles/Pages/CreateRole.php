<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, string> */
    protected array $selectedPermissions = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The form groups its checkboxes for readability; the role itself just
        // takes the flat union. Unsetting the key also keeps Eloquent from
        // trying to fill the `permissions` relation as an attribute.
        $this->selectedPermissions = Arr::flatten($data['permissions'] ?? []);

        unset($data['permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->selectedPermissions);
    }
}
