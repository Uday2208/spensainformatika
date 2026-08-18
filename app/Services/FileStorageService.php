<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Upload a file and return the saved relative path/filename.
     * Supports both local storage and cloud disks seamlessly.
     */
    public static function upload(UploadedFile $file, string $directory = 'uploads'): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        $disk = config('filesystems.default', 'public');

        if ($disk === 'public' || $disk === 'local') {
            // Ensure local directory exists in public/uploads for fallback compatibility
            $publicDir = public_path("uploads/{$directory}");
            if (!File::exists($publicDir)) {
                File::makeDirectory($publicDir, 0755, true, true);
            }
            $file->move($publicDir, $filename);
            
            // Also mirror to storage/app/public if storage link exists
            $storagePublicDir = storage_path("app/public/uploads/{$directory}");
            if (!File::exists($storagePublicDir)) {
                @File::makeDirectory($storagePublicDir, 0755, true, true);
            }
            @File::copy($publicDir . '/' . $filename, $storagePublicDir . '/' . $filename);
        } else {
            // Cloud disk (S3, R2, etc.)
            Storage::disk($disk)->putFileAs("uploads/{$directory}", $file, $filename, 'public');
        }

        return $filename;
    }

    /**
     * Delete a file safely from storage.
     */
    public static function delete(?string $filename, string $directory = 'uploads'): bool
    {
        if (empty($filename)) {
            return false;
        }

        $disk = config('filesystems.default', 'public');

        if ($disk === 'public' || $disk === 'local') {
            $publicPath = public_path("uploads/{$directory}/" . $filename);
            if (File::exists($publicPath)) {
                File::delete($publicPath);
            }

            $storagePath = storage_path("app/public/uploads/{$directory}/" . $filename);
            if (File::exists($storagePath)) {
                @File::delete($storagePath);
            }
            return true;
        } else {
            return Storage::disk($disk)->delete("uploads/{$directory}/" . $filename);
        }
    }

    /**
     * Get public URL of the file.
     */
    public static function url(?string $filename, string $directory = 'uploads'): string
    {
        if (empty($filename)) {
            return '';
        }

        // If filename is already a full URL (e.g. from cloud storage)
        if (Str::startsWith($filename, ['http://', 'https://'])) {
            return $filename;
        }

        $disk = config('filesystems.default', 'public');

        if ($disk === 'public' || $disk === 'local') {
            return asset("uploads/{$directory}/" . $filename);
        }

        return Storage::disk($disk)->url("uploads/{$directory}/" . $filename);
    }
}
