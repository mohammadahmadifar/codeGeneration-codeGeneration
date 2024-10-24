<?php

use Illuminate\Support\Facades\Route;
use YourVendorName\YourPackageName\Http\Controllers\SettingController;

Route::get('/generate-module', [SettingController::class, 'index'])->name('generateModule');
Route::post('/generate-module', function (\Illuminate\Http\Request $request) {
    $moduleSingular = $request->input('singular_name');
    $pluralName = $request->input('plural_name');
    $displayName = $request->input('display_name');
    $fields = json_encode($request->input('fields'));

    \Illuminate\Support\Facades\Artisan::call('make:filter', [
        'moduleSingular' => $moduleSingular,
        'modulePlural' => $pluralName,
        'displayName' => $displayName,
        'fields' => [$fields],
    ]);

    return back()->with('success', 'Module generated successfully!');
})->name('module.generate');
