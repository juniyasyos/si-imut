<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Schema\UserResourceInfolist;
use App\Filament\Resources\UserResource\Schema\UserResourceSchema;
use App\Filament\Resources\UserResource\Tables\UserResourceTable;
use App\Models\User;
use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class UserResource extends Resource implements HasShieldPermissions
{
    use HasActiveIcon;

    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getPermissionPrefixes(): array
    {
        return [
            // Default permissions
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',

            // Custom permissions
            'view_activities',
            'set_role',
            'impersonate',
            'export',
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
            'Role' => $record->roles->first()->name ?? 'No Role',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return route('filament.admin.resources.users.edit', $record);
    }

    public static function getGlobalSearchResultImage(Model $record): ?string
    {
        return $record->profile_photo_url ?? null;
    }

    public static function getLabel(): ?string
    {
        return __('filament-navigation::navigation.resources.users');
    }

    public static function getPluralLabel(): ?string
    {
        return __('');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-navigation::navigation.group.user_access');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(UserResourceSchema::make());
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(UserResourceTable::columns())
            ->filters(UserResourceTable::filters())
            ->actions(UserResourceTable::actions())
            ->bulkActions(UserResourceTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(UserResourceInfolist::infolist());
    }

    public static function getModelLabel(): string
    {
        return __('filament-forms::users.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-forms::users.model.plural_label');
    }
}