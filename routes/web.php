<?php

use Illuminate\Support\Facades\Route;
use App\Providers\Filament\AdminPanelProvider;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

Route::get('/docs', function () {
    return view('docs.swagger');
});

Route::get('/docs/openapi.yaml', function () {
    $path = dirname(base_path()) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'snappie.openapi.yaml';
    return response()->file($path, ['Content-Type' => 'application/yaml']);
});