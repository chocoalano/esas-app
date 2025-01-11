<?php
namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class UploadFile
{
    public static function uploadWithResize($image, $folder)
    {
        // // Generate nama file dengan format unik
        $filename = now()->format('YmdHis') . '.' . $image->getClientOriginalExtension();

        $gambar = Image::read($image);
        $gambar->resizeDown();
        $gambar->save(Storage::disk(env('FILESYSTEM_DISK'))->path("$folder/$filename"));

        return "$folder/$filename";
    }

    public static function unlink($filename)
    {
        try {
            $disk = Storage::disk(env('FILESYSTEM_DISK'));
            if ($disk->exists($filename)) {
                $disk->delete($filename);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
