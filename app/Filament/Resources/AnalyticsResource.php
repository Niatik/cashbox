<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsResource\Pages;
use Filament\Resources\Resource;

class AnalyticsResource extends Resource
{
    protected static ?string $model = null; // No model needed for analytics

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $slug = 'analytics';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('resources.analytics.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('resources.analytics.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.analytics.plural');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\AnalyticsPage::route('/'),
        ];
    }

    // Disable default resource actions since we don't have a model
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
