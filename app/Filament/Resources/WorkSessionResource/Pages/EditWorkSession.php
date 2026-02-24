<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditWorkSession extends EditRecord
{
    protected static string $resource = WorkSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                ...parent::form($form)->getComponents(withHidden: true),
                Forms\Components\Section::make('Расходы смены')
                    ->schema([
                        Forms\Components\Repeater::make('expenseWorkSessions')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('expense_type')
                                    ->label('Тип расхода')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Сумма')
                                    ->required()
                                    ->numeric(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
