<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ImportAction;
use App\Imports\BarangImport;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;




class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;
    protected static ?string $navigationGroup = 'MASTER';
    protected static ?string $navigationLabel = 'Barang';
    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
          

            Select::make('jenis_barang')
            ->label('Jenis Barang')
            ->required()
            ->options([
                'Mini Komputer' => 'Mini Komputer',
                'LAN' => 'LAN',
                'PC Unit' => 'PC Unit',
                'Notebook' => 'Notebook',
                'Speaker' => 'Speaker',
                'Printer' => 'Printer',
                'Scanner' => 'Scanner',
                'Harddisk' => 'Harddisk',
                'PC Server' => 'PC Server',
                'Router' => 'Router',
                'Modem' => 'Modem',
                    ])
            ->searchable(), // Jika ingin bisa dicari

            Forms\Components\TextInput::make('kode_barang')
            ->label('Kode Barang')
            ->required()
            ->maxLength(10)
            ->rule('regex:/^\d+$/'),

            Forms\Components\TextInput::make('nama')
            ->label('Nama Barang')
            ->required()
            ->maxLength(255),

            Forms\Components\TextInput::make('nup')
            ->label('NUP')
            ->required()
            ->maxLength(3)
            ->rule('regex:/^\d+$/'),

            Forms\Components\TextInput::make('penanggungjawab')
            ->label('Penanggung Jawab')
            ->required()
            ->maxLength(255),

            Forms\Components\TextInput::make('ruangan')
            ->label('Ruangan')
            ->required()
            ->maxLength(255),

            Forms\Components\TextInput::make('pengadaan')
            ->label('Pengadaan')
            ->required()
            ->maxLength(255),

            Select::make('kondisi')
            ->label('Kondisi')
            ->required()
            ->options([
                'Baik' => 'Baik',
                'Rusak Berat' => 'Rusak Berat',
                
            ]),

            Forms\Components\TextInput::make('bast')
            ->label('No BAST')
            ->maxLength(10)
            ->rule('regex:/^\d+$/'),

            Forms\Components\TextInput::make('keterangan')
            ->label('Keterangan')
           
            ->maxLength(255),

            Forms\Components\Section::make('Upload Image Section')
                                ->schema([
                                    FileUpload::make('foto')
                                    ->label('Upload Image')
                                    ->image()
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9') // Crop to specific aspect ratio
                                    ->imageResizeTargetWidth(800) // Set a smaller width
                                    ->imageResizeTargetHeight(450) // Set a smaller height
                                    ->disk('public')
                                    ->directory('images')
                                    ->maxSize(5024) // Max 1MB
                            
                                    ->hint('Image size should not exceed 5MB'),
                                  
                                  
                                    
                                ]),
    
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_barang')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kode_barang')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nama')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nup')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('penanggungjawab')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('ruangan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('pengadaan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kondisi')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('foto')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('bast')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('keterangan')->sortable()->searchable(),
              
                
            ])
            ->filters([
                Filter::make('jenis_barang')
                    ->form([
                        Grid::make(20)
                            ->schema([
                                Select::make('jenis_barang')
                                    ->label('Select Jenis Barang')
                                    ->options(Barang::all()->pluck('jenis_barang', 'jenis_barang')->unique())
                                    ->placeholder('Select Jenis Barang')
                                    ->columnSpan(10)
                                    ->searchable()
                                    ->reactive(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['jenis_barang'])) {
                            $query->where('jenis_barang', $data['jenis_barang']);
                        }
                    }),
            
                    Filter::make('kondisi')
                    ->form([
                        Grid::make(20)
                            ->schema([
                                Select::make('kondisi')
                                    ->label('Select Kondisi')
                                    ->options(Barang::all()->pluck('kondisi', 'kondisi')->unique())
                                    ->placeholder('Select Kondisi')
                                    ->columnSpan(10)
                                    ->searchable()
                                    ->reactive(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['kondisi'])) {
                            $query->where('kondisi', $data['kondisi']);
                        }
                    }),
            ], layout: FiltersLayout::AboveContent)
            
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
          
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
            
        ];
    }
}
