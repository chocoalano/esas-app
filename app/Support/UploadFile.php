<?php
namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class UploadFile
{
    public static function uploadWithResize($image, $folder)
    {
        // // Generate nama file dengan format unik
        $filename = now()->format('YmdHis') . '.png';

        $gambar = Image::read($image);
        $gambar->resizeDown();
        $gambar->save(Storage::disk(env('FILESYSTEM_DISK'))->path("$folder/$filename"));

        return "$folder/$filename";
    }
    public static function uploadAttachment($file, $folder)
    {
        // Pastikan parameter $file adalah file yang valid
        if (!$file || !$file->isValid()) {
            throw new \InvalidArgumentException('File tidak valid.');
        }
        // Generate nama file dengan format unik (timestamp + nama asli file)
        $filename = now()->timestamp . '_' . $file->getClientOriginalName();
        // Pastikan folder tidak memiliki slash di awal atau akhir
        $folder = trim($folder, '/');
        // Simpan file di dalam folder yang ditentukan
        $path = Storage::disk(env('FILESYSTEM_DISK', 'local'))->putFileAs($folder, $file, $filename);
        // Kembalikan path dari file yang tersimpan
        return $path;
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
