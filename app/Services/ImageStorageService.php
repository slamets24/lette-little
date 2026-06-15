<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class ImageStorageService
{
    public function storeAsWebp(
        UploadedFile $image,
        string $directory = 'images',
        ?string $disk = null,
        int $quality = 80,
        int $maxWidth = 1600,
    ): string {
        $disk ??= config('filesystems.image_disk', config('filesystems.default'));

        $encoded = Image::decode($image)
            ->scaleDown(width: $maxWidth)
            ->encodeUsingFormat(Format::WEBP, quality: $quality);

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';

        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }
}
