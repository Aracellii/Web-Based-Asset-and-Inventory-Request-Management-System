<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Bagian;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Get;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Account';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'User Management';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('access_user_management');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo('manage_user_management');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_user_management');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_user_management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('User profile and system access data')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter full name'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('user@example.com'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->placeholder('Minimum 8 characters')
                            ->helperText(fn (string $context): string => 
                                $context === 'edit' 
                                    ? 'Leave blank if you do not want to change the password' 
                                    : 'Password must be at least 8 characters'
                            ),

                        Forms\Components\Select::make('role_id')
                            ->label('Role / Position')
                            ->relationship('role', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('The role determines the user permissions in the system')
                            ->live()
                            ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                                'super_admin' => 'Super Admin',
                                'finance' => 'Finance',
                                'admin' => 'Warehouse Admin',
                                'user' => 'User / Staff',
                                default => $record->name,
                            }),

                        Forms\Components\Select::make('bagian_id')
                            ->label('Unit')
                            ->options(Bagian::pluck('nama_bagian', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Work unit'),
                    ])->columns(2),

                Forms\Components\Section::make('Role & Permission Information')
                    ->description('Access rights explained by role')
                    ->schema([
                        Forms\Components\Placeholder::make('role_info')
                            ->label('')
                            ->content(function (Get $get) {
                                $roleId = $get('role_id');
                                
                                if (!$roleId) {
                                    return 'Select a role to view its description';
                                }
                                
                                $role = Role::find($roleId);
                                if (!$role) {
                                    return 'Select a role to view its description';
                                }
                                
                                $roleDescriptions = [
                                    'user' => 'User / Staff: Can create item requests and view their own request status',
                                    'admin' => 'Warehouse Admin: Can manage warehouse stock, approve or reject requests from their unit, and view their unit data',
                                    'finance' => 'Finance: Can view and approve all requests from all units, and view full reports',
                                    'super_admin' => 'Super Admin: Has full access to the entire system, including user and role management',
                                ];
                                
                                return $roleDescriptions[$role->name] ?? $role->name;
                            }),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\BadgeColumn::make('role.name')
                    ->label('Role')
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'finance',
                        'success' => 'admin',
                        'primary' => 'user',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'super_admin',
                        'heroicon-o-currency-dollar' => 'finance',
                        'heroicon-o-wrench' => 'admin',
                        'heroicon-o-user' => 'user',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'finance' => 'Finance',
                        'admin' => 'Warehouse Admin',
                        'user' => 'User / Staff',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('bagian.nama_bagian')
                    ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('Filter Role')
                    ->relationship('role', 'name')
                    ->options(Role::all()->pluck('name', 'id'))
                    ->native(false)
                    ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                        'super_admin' => 'Super Admin',
                        'finance' => 'Finance',
                        'admin' => 'Admin Gudang',
                        'user' => 'User/Staff',
                        default => $record->name,
                    }),

                Tables\Filters\SelectFilter::make('bagian_id')
                    ->label('Filter Bagian')
                    ->options(Bagian::pluck('nama_bagian', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\TrashedFilter::make()
                    ->label('Status')
                    ->native(false),
                    
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete'),
                Tables\Actions\RestoreAction::make()
                    ->label('Restore'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
