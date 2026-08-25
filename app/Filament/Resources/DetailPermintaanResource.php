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
                Forms\Components\Section::make('Edit Requested Item')
                    ->description('Update the requested quantity for this item.')
                    ->schema([
                        Forms\Components\Select::make('barang_id')
                            ->relationship('barang', 'nama_barang')
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->label('Requested Quantity'),
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
        
        // DetailPermintaan is scoped to the user who created the request
        // Super Admin & Finance see everything, Admin sees their division, User sees their own
        return static::applyUserScope($query, 'user_id');
    }
    
}