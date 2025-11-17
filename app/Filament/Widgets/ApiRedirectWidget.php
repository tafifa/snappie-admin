<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;

class ApiRedirectWidget extends Widget
{
  protected static ?string $heading = '🔗 API Links';

  protected int | string | array $columnSpan = 'full';

  protected static ?int $sort = 0;

  protected function getViewData(): array
  {
    $appUrl = env('APP_URL', 'http://localhost:8000');
    return [
      'links' => [
        [
          'label' => 'API Base URL',
          'url' => $appUrl . '/api/v2/health',
          'icon' => 'heroicon-o-server',
          'description' => 'Root API endpoint',
          'color' => 'success',
        ],
        [
          'label' => 'API Documentation',
          'url' => $appUrl . '/docs',
          'icon' => 'heroicon-o-book-open',
          'description' => 'Swagger documentation',
          'color' => 'primary',
        ],
      ],
    ];
  }

  protected static string $view = 'filament.widgets.api-redirect';

  public function getHeading(): string | Htmlable | null
  {
    return static::$heading;
  }
}
