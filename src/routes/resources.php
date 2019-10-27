<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:36
 */

\Illuminate\Support\Facades\Route::namespace(
    'le0daniel\\Laravel\\ImageEngine\\Http\\Controllers'
)->group(function () {

    \Illuminate\Support\Facades\Route::get('/img/{payload}/{signature}.{extension}', 'ImageController@image')
        ->where([
            'payload' => '[a-zA-Z0-9\.\_\-]+',
            'signature' => '[a-z0-9]+',
            'extension' => '(jpg|jpeg|png)'
        ])
        ->name('image-engine.image');
});
