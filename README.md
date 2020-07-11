# Laravel Image Engine

Powerful Image engine for Laravel. Don't waste your time, writing logic to resize and transform images.

## Installation
```
composer require le0daniel/laravel-image-engine
```
After that, publish the configuration file using
```
artisan vendor:publish
```

## Configuration

Configure the desired file sizes your application should provide in your configuration file.

Make sure all the required paths exist within your `image-engine.php` configuration file.

## Usage

The image engine is based on the `ImageRepresentation` class.
```php
use Carbon\Carbon;use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;

$image = ImageRepresentation::from(
    'file/path/relative/to/disk',
    'medium', // Desired image size defined in config
    null,     // Expire Timestamp or Carbon
    'local'   // name of the storage disk where the image is located
);

$imgUrl = image_url($image, 'png' /* Desired output format: jpg | png */);
```

This will generate an Image URL for you, which is signed. The image is only converted on demand, as soon as the first request is made to this url.
The Image will be converted by Intervention Image to the specified format.

For better performance, the images are stored in the public folder. This enables nginx to serve the files once they have been generated. If the Image has an expirey date, the image is stored in the defined path from your config. In this case, php will serve the image, even tho it has been cached.

If you need a file for local processing (Ex: send in an email), simply use:
```php
image_real_path($image, 'png');
```

This will return the local path of the converted image for you.
