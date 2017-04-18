<?php

namespace Providers\File;

use Providers\AbstractProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileProvider extends AbstractProvider
{

    public function getFiles($root, $prefix = '') {

        $files = [];

        if (is_dir($root)) {

            $handle = opendir($root);
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $files[] = $prefix . $file;
            }
            closedir($handle);
        }

        return $files;
    }

    private function getName() {
        return time();
    }

    private function getImageExtension($fileName) {
        $result = getimagesize($fileName);
        switch($result[2]) {
            case IMG_GIF:
                return 'gif';
            case IMG_JPG:
            case IMG_JPEG:
                return 'jpg';
            case IMG_PNG:
                return 'png';
        }

        return false;
    }

    public function saveImage($root, UploadedFile $image, $rewrite = false) {

        if (!is_dir($root)) {
            mkdir($root, 0777, true);
        }

        $fileName = $this->getName();
        $extension = $this->getImageExtension($image->getPathname());
        if ($extension === false) {
            return false;
        }
        $extension = '.'.$extension;

        $i = 0;

        if ($rewrite) {

            if (file_exists($root.'/'.$fileName.$extension)) {
                unlink($root.'/'.$fileName.$extension);
            }

        } else {

            while (file_exists($root.'/'.$fileName.($i > 0 ? '_'.$i : '').$extension)) {
                $i++;
            }
        }

        $fileName = $fileName.($i > 0 ? '_'.$i : '').$extension;

        $image->move($root, $fileName);

        return $fileName;
    }

    public function registerExtension()
    {
        $this->app['fileManager'] = $this;
    }
}