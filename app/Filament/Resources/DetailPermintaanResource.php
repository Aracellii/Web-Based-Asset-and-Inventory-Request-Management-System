<?php

namespace App\Filament\Resources;

use App\Models\DetailPermintaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Resources\DetailPermintaanResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasBagianScope;

class DetailPermintaanResource extends Resource
{
    use HasBagianScope;
    
    protected static ?string $model = DetailPermintaan::class;
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Edit Item Barang')
                    ->description('Silahkan ubah jumlah permintaan barang.')
                    ->schema([
                        Forms\Components\Select::make('barang_id')
                            ->relationship('barang', 'nama_barang')
                            ->required()
                            ->disabled() // Barang dikunci, hanya jumlah yang boleh edit
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->label('Jumlah yang diminta'),
                    ])->columns(2)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailPermintaans::route('/'),
            'edit' => Pages\EditDetailPermintaan::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // DetailPermintaan di-scope berdasarkan user yang membuat permintaan
        // Super Admin & Keuangan lihat semua, Admin lihat bagiannya, User lihat miliknya
        return static::applyUserScope($query, 'user_id');
    }
    
}