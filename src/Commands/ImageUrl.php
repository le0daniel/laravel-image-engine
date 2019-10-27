<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 13:44
 */

namespace le0daniel\Laravel\ImageEngine\Commands;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use le0daniel\Laravel\ImageEngine\Image\Image;

class ImageUrl extends Command
{

    /**
     * Signature of the command
     *
     * @var string
     */
    protected $signature = 'image:url {path} {size} {disk=local} {extension?}';

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle()
    {
        $path = $this->argument('path');
        $size = $this->argument('size');
        $disk = $this->argument('disk');
        $extension = $this->argument('extension');

        $image = new Image(
            $path,
            $size,
            Carbon::now()->addMinutes(10),
            $disk
        );

        if (!Storage::disk($disk)->exists($path)) {
            return $this->line('Image not found. Path ' . $path . ' on disk ' . $disk, 'error');
        }

        if (!$extension) {
            $extension = pathinfo($path, PATHINFO_EXTENSION) ?? 'jpg';
            $this->line("No extension given, using <info>{$extension}</info>");
        }

        // Force
        $this->line('Generating URL for file: ' . $path, 'info');
        $this->line(
            image_url($image, $extension)
        );

        //$this->line('u: '. $disk);

    }

}