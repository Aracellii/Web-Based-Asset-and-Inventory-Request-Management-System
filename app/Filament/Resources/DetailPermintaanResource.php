<?php

namespace App\Filament\Resources;

use App\Models\DetailPermintaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Resources\DetailPermintaanResource\Pages;

class DetailPermintaanResource extends Resource
{
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
    
}