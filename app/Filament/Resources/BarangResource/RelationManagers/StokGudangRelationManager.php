<?php

namespace App\Filament\Resources\BarangResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StokGudangRelationManager extends RelationManager
{
    protected static string $relationship = 'gudangs';

    protected static ?string $title = 'Stok Per Bidang';

    protected static ?string $recordTitleAttribute = 'stok';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bagian_id')
                    ->relationship('bagian', 'nama_bagian')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('stok')
                    ->label('Jumlah Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stok')
            ->columns([
                Tables\Columns\TextColumn::make('bagian.nama_bagian')
                    ->label('Bidang / Bagian')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Jumlah Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tidak perlu create karena sudah otomatis dibuat saat barang dibuat
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin'])),
            ])
            ->bulkActions([
                //
            ]);
    }
}
