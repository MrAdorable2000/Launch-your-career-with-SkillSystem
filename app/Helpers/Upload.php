<?php

namespace App\Helpers;

class Upload
{
    public static function handle(array $file, string $destination, array $allowedTypes = ['jpg','jpeg','png','gif','pdf','doc','docx']): array
    {
        $result = ['success' => false, 'message' => '', 'path' => ''];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['message'] = 'File upload failed.';
            return $result;
        }

        $maxSize = (int) self::getMaxUploadSize();
        if ($file['size'] > $maxSize) {
            $result['message'] = 'File size exceeds the maximum allowed limit.';
            return $result;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            $result['message'] = 'File type is not allowed.';
            return $result;
        }

        $filename = uniqid('file_', true) . '.' . $ext;
        $fullPath = ROOT_PATH . '/public/assets/' . $destination . '/' . $filename;

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            $result['success'] = true;
            $result['path'] = $destination . '/' . $filename;
            $result['message'] = 'File uploaded successfully.';
        } else {
            $result['message'] = 'Failed to move uploaded file.';
        }

        return $result;
    }

    private static function getMaxUploadSize(): int
    {
        $max = ini_get('upload_max_filesize');
        $postMax = ini_get('post_max_size');
        return min(self::parseSize($max), self::parseSize($postMax));
    }

    private static function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[-1]);
        $val = (int) $size;
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    public static function delete(string $path): bool
    {
        $fullPath = ROOT_PATH . '/public/assets/' . ltrim($path, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}