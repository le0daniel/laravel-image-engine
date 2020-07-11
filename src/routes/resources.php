<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:36
 */

use le0daniel\Laravel\ImageEngine\Http\Controllers\ImageController;

\Illuminate\Support\Facades\Route::get('/img/{folder}/{path}.{extension}', [ImageController::class, 'image'])
    ->where(
        [
            'folder' => '[a-zA-Z0-9\_\-]+',
            'path' => '[a-zA-Z0-9\_\-]+',
            'extension' => '(jpg|jpeg|png)',
        ]
    )
    ->name('image-engine.image');
