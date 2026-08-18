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
        // Deteksi apakah sedang berjalan di lingkungan Serverless Read-Only (Vercel / Lambda)
        $isServerless = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || file_exists('/var/task');

        if ($isServerless) {
            // Coba unggah ke Cloud Image Provider (ImgBB Free API)
            try {
                $apiKey = env('IMGBB_API_KEY', '7b4e2f6990d5658e454ebbbbe56587d5'); // Built-in Free API Key
                $base64Image = base64_encode(file_get_contents($file->getRealPath()));

                $response = \Illuminate\Support\Facades\Http::asForm()->timeout(15)->post('https://api.imgbb.com/1/upload', [
                    'key' => $apiKey,
                    'image' => $base64Image,
                    'name' => 'spensa_' . time() . '_' . Str::random(6),
                ]);

                if ($response->successful() && isset($response->json()['data']['url'])) {
                    return $response->json()['data']['url']; // Mengembalikan Direct HTTPS URL dari Cloud
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('ImgBB upload error: ' . $e->getMessage());
            }

            // Fallback kompresi Base64 ringan jika cloud API gagal
            $mime = $file->getMimeType() ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        $disk = config('filesystems.default') ?: 'public';

        if (empty($disk) || $disk === 'public' || $disk === 'local' || !config()->has("filesystems.disks.{$disk}")) {
            // Environment Local (XAMPP): Simpan ke public_path
            $publicDir = public_path("uploads/{$directory}");
            if (!File::exists($publicDir)) {
                @File::makeDirectory($publicDir, 0755, true, true);
            }
            $file->move($publicDir, $filename);
            
            // Mirroring ke storage/app/public jika ada
            $storagePublicDir = storage_path("app/public/uploads/{$directory}");
            if (!File::exists($storagePublicDir)) {
                @File::makeDirectory($storagePublicDir, 0755, true, true);
            }
            @File::copy($publicDir . '/' . $filename, $storagePublicDir . '/' . $filename);
        } else {
            // Cloud disk (S3, R2, dll.)
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

        // Jika data adalah Base64 Data URI, tidak ada file fisik yang perlu dihapus
        if (Str::startsWith($filename, 'data:image')) {
            return true;
        }

        $disk = config('filesystems.default') ?: 'public';

        if (empty($disk) || $disk === 'public' || $disk === 'local' || !config()->has("filesystems.disks.{$disk}")) {
            $publicPath = public_path("uploads/{$directory}/" . $filename);
            if (File::exists($publicPath)) {
                @File::delete($publicPath);
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

        // Jika gambar disimpan sebagai Base64 atau URL eksternal, kembalikan langsung stringnya
        if (Str::startsWith($filename, ['data:image', 'http://', 'https://'])) {
            return $filename;
        }

        $disk = config('filesystems.default') ?: 'public';

        if (empty($disk) || $disk === 'public' || $disk === 'local' || !config()->has("filesystems.disks.{$disk}")) {
            return asset("uploads/{$directory}/" . $filename);
        }

        return Storage::disk($disk)->url("uploads/{$directory}/" . $filename);
    }
}
