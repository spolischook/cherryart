<?php

namespace Cherry\Form\DataTransformer;

use Cherry\ImageHandler;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageCollectionTransformer implements DataTransformerInterface
{
    /**
     * @var ImageHandler
     */
    protected $imageHandler;

    /**
     * @var string
     */
    protected $imageType;

    public function __construct(ImageHandler $imageHandler, $imageType)
    {
        $this->imageHandler = $imageHandler;
        $this->imageType    = $imageType;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        if (null === $data) {
            return null;
        }

        $data['images']  = $this->transformImages($data);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        $data['images']  = $this->reverseTransformImages($data);

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function transformImages(array $data)
    {
        if (!$data['images']) {
            return [];
        }

        return array_map(function ($filename) {
            return new File($filename, false);
        }, explode(',', $data['images']));
    }

    /**
     * @param array $data
     * @return null|string
     */
    protected function reverseTransformImages(array $data)
    {
        if (!$data['images']) {
            return null;
        }

        return implode(',', array_filter(array_map(function ($file) use ($data) {
            if (null === $file) {
                return null;
            }

            if (UploadedFile::class === get_class($file)) {
                return $this->imageHandler->upload(
                    $file,
                    $this->imageType,
                    uniqid($data['slug'].'_')
                );
            }

            if (File::class === get_class($file)) {
                return $file->getPathName();
            }

            throw new UnexpectedTypeException($file, 'null|UploadedFile|File');

        }, $data['images'])));
    }
}
