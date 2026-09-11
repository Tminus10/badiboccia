<?php

final class PhotoUploader
{
    private const MAX_BYTES = 8 * 1024 * 1024;
    private const TARGET_SIZE = 500;

    /**
     * Crops the uploaded image to a centered square and saves it as uploads/teams/{id}.jpg
     * @return string relative path (from public/) to store as teams.photo_path
     * @throws RuntimeException on invalid upload
     */
    public static function handle(int $teamId, array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload fehlgeschlagen.');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new RuntimeException('Bild ist zu gross (max. 8 MB).');
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            throw new RuntimeException('Datei ist kein gültiges Bild.');
        }

        $source = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png' => imagecreatefrompng($file['tmp_name']),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file['tmp_name']) : false,
            default => false,
        };
        if ($source === false) {
            throw new RuntimeException('Bildformat wird nicht unterstützt (nutze JPG, PNG oder WEBP).');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $srcX = (int) (($width - $side) / 2);
        $srcY = (int) (($height - $side) / 2);

        $target = imagecreatetruecolor(self::TARGET_SIZE, self::TARGET_SIZE);
        imagecopyresampled($target, $source, 0, 0, $srcX, $srcY, self::TARGET_SIZE, self::TARGET_SIZE, $side, $side);

        $dir = __DIR__ . '/../../public/uploads/teams';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = "team-$teamId.jpg";
        imagejpeg($target, "$dir/$filename", 85);

        imagedestroy($source);
        imagedestroy($target);

        return "uploads/teams/$filename";
    }
}
