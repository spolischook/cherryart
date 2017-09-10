<?php

namespace Cherry;

use Intervention\Image\Exception\NotFoundException;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @param string $webThumbnailPath
     * @param array $formats
     */
    public function __construct(string $originalPath, string $thumbnailPath, string $webThumbnailPath, array $formats)
    {
        if (!is_dir($originalPath)) {
            throw new NotFoundException(sprintf('Original image path "%s" not found', $originalPath));
        }

        if (!is_dir($thumbnailPath)) {
            throw new NotFoundException(sprintf('Thumbnail image path "%s" not found', $thumbnailPath));
        }

        $this->originalPath = $originalPath;
        $this->thumbnailPath = $thumbnailPath;
        $this->webThumbnailPath = $webThumbnailPath;
        $this->formats = $formats;
        $this->imageManager = new ImageManager(array('driver' => 'imagick'));
    }

    public function getImageUrl(string $fileName, string $format): string
    {
        return sprintf('%s/%s/%s', $this->webThumbnailPath, $format, $fileName);
    }

    public function getOriginal(string $filename): string
    {
        return $this->originalPath.'/'.$filename;
    }

    public function getThumbnail(string $filename, string $format): string
    {
        return $this->thumbnailPath.'/'.$format.'/'.$filename;
    }

    public function upload(UploadedFile $file, string $subDirectory, string $name): string
    {
        $this->mkOriginDir($subDirectory);
        $newFileName = $name.'.'.strtolower($file->guessClientExtension());

        $file->move($this->originalPath.'/'.$subDirectory, $newFileName);
        $this->createThumbnails($newFileName, $subDirectory);

        return $subDirectory.'/'.$newFileName;
    }

    /**
     * @param string $originalFileName
     * @param string $subDirectory
     */
    public function createThumbnails(string $originalFileName, string $subDirectory)
    {
        $this->mkThumbnailDirs($subDirectory);

        $image = $this->imageManager->make($this->originalPath.'/'.$subDirectory.'/'.$originalFileName);
        $image->backup();

        foreach ($this->formats as $format => $closure) {
            $image->reset();
            $closure($image);

            $image->save($this->thumbnailPath.'/'.$format.'/'.$subDirectory.'/'.$originalFileName);
        }
    }

    /**
     * @return string
     */
    public function getOriginalPath(): string
    {
        return $this->originalPath;
    }

    /**
     * @param string $subDirectory
     */
    protected function mkOriginDir(string $subDirectory)
    {
        $dir = $this->originalPath.'/'.$subDirectory;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    /**
     * @param string $subDirectory
     */
    protected function mkThumbnailDirs(string $subDirectory)
    {
        foreach (array_keys($this->formats) as $format) {
            $dir = $this->thumbnailPath.'/'.$format.'/'.$subDirectory;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    /**
     * @return string
     */
    public function getWebThumbnailPath():string
    {
        return $this->webThumbnailPath;
    }
}
