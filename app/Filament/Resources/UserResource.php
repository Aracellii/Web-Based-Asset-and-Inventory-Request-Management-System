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
      protected static ?string $navigationGroup = 'Akun';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manajemen User';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('akses_managemen_user');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo('manage_manajemen_user');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_manajemen_user');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_manajemen_user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi User')
                    ->description('Data pengguna dan akses sistem')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama lengkap'),

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
                            ->placeholder('Minimal 8 karakter')
                            ->helperText(fn (string $context): string => 
                                $context === 'edit' 
                                    ? 'Kosongkan jika tidak ingin mengubah password' 
                                    : 'Password harus minimal 8 karakter'
                            ),

                        Forms\Components\Select::make('role_id')
                            ->label('Role / Jabatan')
                            ->relationship('role', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Role untuk menentukan hak akses user dalam sistem')
                            ->live()
                            ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                                'super_admin' => 'Super Admin',
                                'keuangan' => 'Keuangan',
                                'admin' => 'Admin Gudang',
                                'user' => 'User/Staff',
                                default => $record->name,
                            }),

                        Forms\Components\Select::make('bagian_id')
                            ->label('Bagian')
                            ->options(Bagian::pluck('nama_bagian', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Unit Kerja'),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Role & Permission')
                    ->description('Penjelasan hak akses berdasarkan role')
                    ->schema([
                        Forms\Components\Placeholder::make('role_info')
                            ->label('')
                            ->content(function (Get $get) {
                                $roleId = $get('role_id');
                                
                                if (!$roleId) {
                                    return 'Pilih role untuk melihat deskripsi';
                                }
                                
                                $role = Role::find($roleId);
                                if (!$role) {
                                    return 'Pilih role untuk melihat deskripsi';
                                }
                                
                                $roleDescriptions = [
                                    'user' => 'User/Staff: Dapat membuat permintaan barang dan melihat status permintaan sendiri',
                                    'admin' => 'Admin Gudang: Dapat mengelola stok gudang, approve/reject permintaan dari bagiannya, dan melihat data bagiannya',
                                    'keuangan' => 'Keuangan: Dapat melihat dan approve semua permintaan dari semua bagian, serta melihat laporan lengkap',
                                    'super_admin' => 'Super Admin: Memiliki akses penuh ke seluruh sistem termasuk manajemen user dan role',
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
                    ->label('Nama')
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
                        'warning' => 'keuangan',
                        'success' => 'admin',
                        'primary' => 'user',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'super_admin',
                        'heroicon-o-currency-dollar' => 'keuangan',
                        'heroicon-o-wrench' => 'admin',
                        'heroicon-o-user' => 'user',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'keuangan' => 'Keuangan',
                        'admin' => 'Admin Gudang',
                        'user' => 'User/Staff',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('bagian.nama_bagian')
                    ->label('Bagian')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
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
                        'keuangan' => 'Keuangan',
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
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
                Tables\Actions\RestoreAction::make()
                    ->label('Pulihkan'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
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
