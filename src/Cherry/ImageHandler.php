<?php

namespace Cherry;

use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ImageHandler
{
    const TYPE_ART_WORK = 'art_work';

    /** @var  string */
    protected $originalPath;

    /** @var  string */
    protected $thumbnailPath;

    /** @var  string */
    protected $webThumbnailPath;

    /** @var  array */
    protected $formats;

    /** @var  ImageManager */
    protected $imageManager;

    /**
     * ImageManager constructor.
     * @param string $originalPath
     * @param string $thumbnailPath
     * @param array $formats
     */
    public function __construct($originalPath, $thumbnailPath, $webThumbnailPath, array $formats)
    {
        $this->originalPath = $originalPath;
        $this->thumbnailPath = $thumbnailPath;
        $this->webThumbnailPath = $webThumbnailPath;
        $this->formats = $formats;
        $this->imageManager = new ImageManager(array('driver' => 'imagick'));
    }

    public function getImageUrl($fileName, $type, $format)
    {
        return sprintf('%s/%s/%s/%s', $this->webThumbnailPath, $type, $format, $fileName);
    }

    public function getOriginal($filename, $type)
    {
        return $this->originalPath.'/'.$type.'/'.$filename;
    }

    public function getThumbnail($filename, $type, $format)
    {
        return $this->thumbnailPath.'/'.$type.'/'.$format.'/'.$filename;
    }

    public function upload(UploadedFile $file, $type, $name)
    {
        $this->mkOriginDir($type);
        $newFileName = $name.'.'.$file->getClientOriginalExtension();

        $file->move($this->originalPath.'/'.$type, $newFileName);
        $this->createThumbnails($newFileName, $type);

        return $newFileName;
    }

    public function createThumbnails($originalFileName, $type)
    {
        $this->mkThumbnailDirs($type);

        $image = $this->imageManager->make($this->originalPath.'/'.$type.'/'.$originalFileName);
        $image->backup();

        foreach ($this->formats as $format => $closure) {
            $image->reset();
            $closure($image);

            $image->save($this->thumbnailPath.'/'.$type.'/'.$format.'/'.$originalFileName);
        }
    }

    protected function mkOriginDir($type)
    {
        $dir = $this->originalPath.'/'.$type;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    protected function mkThumbnailDirs($type)
    {
        foreach (array_keys($this->formats) as $format) {
            $dir = $this->thumbnailPath.'/'.$type.'/'.$format;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }
}
