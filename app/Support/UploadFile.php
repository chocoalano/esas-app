<?php
namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class UploadFile
{
    public static function uploadWithResize($image, $folder, ?string $rename = null)
    {
        try {
            // Pastikan direktori penyimpanan tersedia
            Storage::disk(env('FILESYSTEM_DISK'))->makeDirectory($folder);
            $filename = $rename ? "$rename.png" : now()->format('YmdHis') . ".png";

            // Membaca dan meresize gambar dengan Intervention Image
            $gambar = Image::make($image->getRealPath());
            $gambar->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Simpan gambar ke storage
            $path = Storage::disk(env('FILESYSTEM_DISK'))->path("$folder/$filename");
            $gambar->save($path, 90); // Simpan dengan kualitas 90%

            return "$folder/$filename";
        } catch (\Exception $e) {
            throw new \RuntimeException("Gagal mengupload dan meresize gambar: " . $e->getMessage());
        }
    }
    public static function uploadAttachment($file, $folder)
    {
        // Pastikan parameter $file adalah file yang valid
        if (!$file || !$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file provided.');
        }

        // Generate nama file dengan format unik (timestamp + nama asli file)
        $timestamp = now()->timestamp;
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $sanitizedOriginalName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName); // Hindari karakter khusus
        $filename = "{$timestamp}_{$sanitizedOriginalName}.{$extension}";

        // Pastikan folder tidak memiliki slash di awal atau akhir
        $folder = trim($folder, '/');

        // Simpan file di dalam folder yang ditentukan
        try {
            $disk = Storage::disk(env('FILESYSTEM_DISK', 'local'));
            $disk->putFileAs($folder, $file, $filename);

            // Return full relative path in "$folder/$filename" format
            return "$folder/$filename";
        } catch (\Exception $e) {
            // Tangani kesalahan penyimpanan file
            throw new \RuntimeException("Failed to upload the file: " . $e->getMessage());
        }

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
