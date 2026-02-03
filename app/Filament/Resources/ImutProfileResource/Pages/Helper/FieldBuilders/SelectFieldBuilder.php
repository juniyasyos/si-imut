<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Str;

/**
 * Builder for select fields (single and multi)
 */
class SelectFieldBuilder
{
    /**
     * Create a single select field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param array $options Options array
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return ToggleButtons
     */
    public static function createSingleSelect(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        array $options = [],
        bool $required = false,
        $visibleCondition = true
    ): ToggleButtons {
        return ToggleButtons::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->options($options)
            ->inline()
            ->required($required)
            ->visible($visibleCondition)
            ->live();
    }

    /**
     * Create a multi select field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param array $options Options array
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return CheckboxList
     */
    public static function createMultiSelect(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        array $options = [],
        bool $required = false,
        $visibleCondition = true
    ): CheckboxList {
        return CheckboxList::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->options($options)
            ->required($required)
            ->bulkToggleable()
            ->visible($visibleCondition)
            ->live()
            ->columns(1);
    }

    /**
     * Create a searchable select field with custom input support and add new options capability
     *
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param array $options Options array
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @param string|null $defaultValue Default value
     * @param callable|null $onAddCallback Callback when new option is added
     * @return Select
     */
    public static function createSearchableSelect(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        array $options = [],
        bool $required = false,
        $visibleCondition = true,
        ?callable $onAddCallback = null
    ): Select {
        $select = Select::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->searchable()
            ->required($required)
            ->visible($visibleCondition);

        // Always allow custom input and adding new options
        $select->options($options)
            ->placeholder('Pilih dari history atau ketik input custom')
            ->allowHtml()
            ->suffixAction(
                Action::make('addNewOption')
                    ->label('Tambah Baru')
                    ->icon('heroicon-o-plus')
                    ->size(ActionSize::Small)
                    ->color('success')
                    ->form([
                        TextInput::make('new_option_label')
                            ->label('Opsi Baru')
                            ->required()
                            ->placeholder('Masukkan opsi baru')
                            ->maxLength(255)
                            ->helperText('Nilai yang dimasukkan akan digunakan sebagai label dan value'),
                    ])
                    ->modalHeading('Tambah Opsi Baru')
                    ->modalDescription('Tambahkan opsi baru ke dalam daftar pilihan.')
                    ->modalSubmitActionLabel('Tambah Opsi')
                    ->action(function (array $data, $set, $get, $livewire) use ($fieldKey, $onAddCallback) {
                        $newLabel = trim($data['new_option_label']);
                        $newValue = $newLabel;

                        if (empty($newLabel)) {
                            return;
                        }

                        // Get current options
                        $currentOptions = $get($fieldKey . '_options') ?? $get('data.' . $fieldKey . '_options') ?? [];

                        // Check if value already exists
                        if (isset($currentOptions[$newValue])) {
                            // Show error notification
                            \Filament\Notifications\Notification::make()
                                ->title('Opsi sudah ada')
                                ->body('Opsi dengan value tersebut sudah ada dalam daftar.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Add new option to current options
                        $currentOptions[$newValue] = $newLabel;

                        // Update the options (try different paths)
                        $set($fieldKey . '_options', $currentOptions);
                        $set('data.' . $fieldKey . '_options', $currentOptions);

                        // Set the selected value to the new option
                        $set($fieldKey, $newValue);
                        $set('data.' . $fieldKey, $newValue);

                        // Call callback if provided
                        if ($onAddCallback) {
                            $onAddCallback($newValue, $newLabel, $currentOptions);
                        }

                        // Show success notification
                        \Filament\Notifications\Notification::make()
                            ->title('Opsi berhasil ditambahkan')
                            ->body("Opsi '{$newLabel}' telah ditambahkan ke daftar.")
                            ->success()
                            ->send();
                    })
            );

        return $select;
    }

    /**
     * Extract options from field options collection
     * 
     * @param mixed $fieldOptions Field options
     * @return array
     */
    public static function extractOptions($fieldOptions): array
    {
        $options = [];
        foreach ($fieldOptions as $option) {
            $options[$option->option_value] = $option->option_text;
        }
        return $options;
    }
}
