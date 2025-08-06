<?php

namespace App\Models;

class Image
{
    private $uploadDir = "./image/";

    public function __construct()
    {
        // Yükleme klasörünü oluştur
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function upload($file)
    {
        // Dosya türü kontrolü
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['error' => 'Invalid file type'];
        }

        // Rastgele dosya adı oluştur
        $randomName = uniqid();

        // Görseli boyutlandır ve JPG ile WEBP versiyonlarını oluştur
        $paths = [
            'jpg' => [
                'lg' => $this->resizeImage($file['tmp_name'], 500, 'lg_' . $randomName, 'jpg'),
                'md' => $this->resizeImage($file['tmp_name'], 200, 'md_' . $randomName, 'jpg'),
                'sm' => $this->resizeImage($file['tmp_name'], 60, 'sm_' . $randomName, 'jpg'),
            ],
            'webp' => [
                'lg' => $this->resizeImage($file['tmp_name'], 500, 'lg_' . $randomName, 'webp'),
                'md' => $this->resizeImage($file['tmp_name'], 200, 'md_' . $randomName, 'webp'),
                'sm' => $this->resizeImage($file['tmp_name'], 60, 'sm_' . $randomName, 'webp'),
            ],
        ];

        return $paths;
    }

    private function resizeImage($filePath, $width, $newFileName, $format)
    {
        // Görseli yükle
        list($originalWidth, $originalHeight, $type) = getimagesize($filePath);
        $ratio = $originalWidth / $originalHeight;
        $height = $width / $ratio;

        // float değerleri int'e dönüştür
        $width = intval($width);
        $height = intval($height);

        $newImage = imagecreatetruecolor($width, $height);

        // Kaynak görseli oluştur
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }

        // Görseli yeniden boyutlandır
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        // Yeni dosyayı kaydet
        $newFilePath = $this->uploadDir . $newFileName . '.' . $format;
        if ($format === 'jpg') {
            imagejpeg($newImage, $newFilePath, 90);
        } elseif ($format === 'webp') {
            imagewebp($newImage, $newFilePath, 90);
        }

        // Belleği temizle
        imagedestroy($newImage);
        imagedestroy($source);

        return $newFilePath;
    }
}
