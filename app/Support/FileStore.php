<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * حفظ الملفات الخاصة (صور التحويلات والبروفايل).
 *
 * - محلياً: على القرص (storage/app/private).
 * - على الاستضافة (FILE_STORE=database): جوّا قاعدة البيانات، لأنه قرص
 *   الاستضافة المجانية بينمسح كل ما يرجع السيرفر يشتغل.
 * الصور بتتصغّر قبل الحفظ عشان ما تاخد مساحة كبيرة.
 */
class FileStore
{
    private const TABLE = 'stored_files';

    private const MAX_SIDE = 1600;

    public static function usesDatabase(): bool
    {
        return config('isnad.file_store') === 'database';
    }

    /** بيحفظ الملف وبيرجّع مساره (زي receipts/abc.jpg) */
    public static function put(UploadedFile $file, string $dir): string
    {
        [$bytes, $ext, $mime] = self::prepare($file);
        $path = trim($dir, '/').'/'.Str::random(40).'.'.$ext;

        if (self::usesDatabase()) {
            DB::table(self::TABLE)->insert([
                'path' => $path,
                'mime' => $mime,
                'size' => strlen($bytes),
                'data' => base64_encode($bytes),
                'created_at' => now(),
            ]);
        } else {
            Storage::disk('local')->put($path, $bytes);
        }

        return $path;
    }

    public static function exists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return self::usesDatabase()
            ? DB::table(self::TABLE)->where('path', $path)->exists()
            : Storage::disk('local')->exists($path);
    }

    public static function response(string $path, array $headers = []): Response
    {
        if (! self::usesDatabase()) {
            return Storage::disk('local')->response($path, null, $headers);
        }

        $row = DB::table(self::TABLE)->where('path', $path)->first();
        abort_unless($row, 404);
        $bytes = base64_decode($row->data);

        return response($bytes, 200, $headers + [
            'Content-Type' => $row->mime,
            'Content-Length' => strlen($bytes),
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }
        self::usesDatabase()
            ? DB::table(self::TABLE)->where('path', $path)->delete()
            : Storage::disk('local')->delete($path);
    }

    public static function deleteDirectory(string $dir): void
    {
        self::usesDatabase()
            ? DB::table(self::TABLE)->where('path', 'like', trim($dir, '/').'/%')->delete()
            : Storage::disk('local')->deleteDirectory($dir);
    }

    /** الصور بتتصغّر لـ 1600px وبتنحفظ JPG؛ الـ PDF بيضل زي ما هو */
    private static function prepare(UploadedFile $file): array
    {
        $bytes = (string) file_get_contents($file->getRealPath());
        $ext = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $mime = $file->getMimeType() ?: 'application/octet-stream';

        if (! str_starts_with($mime, 'image/') || ! function_exists('imagecreatefromstring')) {
            return [$bytes, $ext, $mime];
        }

        $img = @imagecreatefromstring($bytes);
        if (! $img) {
            return [$bytes, $ext, $mime];
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::MAX_SIDE / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255)); // خلفية بيضا للصور الشفافة
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagejpeg($out, null, 82);
        $jpg = (string) ob_get_clean();

        // إذا الأصلية أصغر، منخليها
        return strlen($jpg) > 0 && strlen($jpg) < strlen($bytes)
            ? [$jpg, 'jpg', 'image/jpeg']
            : [$bytes, $ext, $mime];
    }
}
