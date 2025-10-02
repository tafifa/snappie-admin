<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $additional = $this->record->additional_info ?? [];

        if (is_string($additional)) {
            $decoded = json_decode($additional, true);
            $additional = is_array($decoded) ? $decoded : [];
        }

        $saved = Arr::get($additional, 'user_saved', []);

        $items = [];
        foreach ($saved as $type => $ids) {
            if (!is_array($ids)) {
                continue;
            }

            foreach ($ids as $id) {
                if ($id === null || $id === '') {
                    continue;
                }

                $items[] = [
                    'type' => $type,
                    'item_id' => $id,
                ];
            }
        }

        $data['saved_items'] = $items;

        return parent::mutateFormDataBeforeFill($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                ->color('info'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existing = $this->record->additional_info ?? [];

        if (is_string($existing)) {
            $decoded = json_decode($existing, true);
            $existing = is_array($decoded) ? $decoded : [];
        }

        $savedItems = Arr::pull($data, 'saved_items', []);

        $grouped = [
            'saved_places' => [],
            'saved_posts' => [],
            'saved_articles' => [],
        ];

        foreach ($savedItems as $item) {
            $type = Arr::get($item, 'type');
            $id = Arr::get($item, 'item_id');

            if (!is_string($type) || !array_key_exists($type, $grouped) || $id === null || $id === '') {
                continue;
            }

            $value = is_numeric($id) ? (int) $id : $id;

            if (!in_array($value, $grouped[$type], true)) {
                $grouped[$type][] = $value;
            }
        }

        $incoming = Arr::get($data, 'additional_info', []);
        if (!is_array($incoming)) {
            $incoming = [];
        }

        $incoming['user_saved'] = $grouped;

        $data['additional_info'] = array_replace_recursive($existing, $incoming);

        return $data;
    }
}
