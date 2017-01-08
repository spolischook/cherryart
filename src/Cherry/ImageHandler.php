<?php

namespace Cherry;

use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ImageHandler
{
    const TYPE_ART_WORK = 'art_work';
    const TYPE_NEWS     = 'news';

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

    public function getImageUrl($fileName, $format)
    {
        return sprintf('%s/%s/%s', $this->webThumbnailPath, $format, $fileName);
    }

    public function getOriginal($filename)
    {
        return $this->originalPath.'/'.$filename;
    }

    public function getThumbnail($filename, $format)
    {
        return $this->thumbnailPath.'/'.$format.'/'.$filename;
    }

    public function upload(UploadedFile $file, $subDirectory, $name)
    {
        $this->mkOriginDir($subDirectory);
        $newFileName = $name.'.'.strtolower($file->getClientOriginalExtension());

        $file->move($this->originalPath.'/'.$subDirectory, $newFileName);
        $this->createThumbnails($newFileName, $subDirectory);

        return $newFileName;
    }

    public function createThumbnails($originalFileName, $subDirectory)
    {
        $this->mkThumbnailDirs($subDirectory);

        $image = $this->imageManager->make($this->originalPath.'/'.$subDirectory.'/'.$originalFileName);
        $image->backup();

        foreach ($this->formats as $format => $closure) {
            $image->reset();
            $closure($image);

            $image->save($this->thumbnailPath.'/'.$subDirectory.'/'.$format.'/'.$originalFileName);
        }
    }

    /**
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    protected function mkOriginDir($subDirectory)
    {
        $dir = $this->originalPath.'/'.$subDirectory;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    protected function mkThumbnailDirs($subDirectory)
    {
        foreach (array_keys($this->formats) as $format) {
            $dir = $this->thumbnailPath.'/'.$subDirectory.'/'.$format;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }
}
