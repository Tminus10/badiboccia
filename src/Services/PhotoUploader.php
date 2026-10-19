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
            throw new RuntimeException(t('Upload fehlgeschlagen.'));
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new RuntimeException(t('Bild ist zu gross (max. 8 MB).'));
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            throw new RuntimeException(t('Datei ist kein gültiges Bild.'));
        }

        $source = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png' => imagecreatefrompng($file['tmp_name']),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file['tmp_name']) : false,
            default => false,
        };
        if ($source === false) {
            throw new RuntimeException(t('Bildformat wird nicht unterstützt (nutze JPG, PNG oder WEBP).'));
        }
        if ($info['mime'] === 'image/jpeg') {
            $source = self::applyExifOrientation($source, $file['tmp_name']);
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

        return "uploads/teams/$filename";
    }

    /**
     * Phone cameras store JPEGs unrotated plus an EXIF orientation flag; GD ignores that flag,
     * so without this a portrait phone photo would end up sideways. No-op without ext-exif.
     */
    private static function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        $angle = match ($orientation) {
            3, 4 => 180,
            6, 7 => 270,
            5, 8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, 0);
        return $rotated !== false ? $rotated : $image;
    }
}
