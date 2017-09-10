<?php

namespace Cherry\Form\DataTransformer;

use Cherry\ImageHandler;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
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
     * @var array
     */
    protected $images;

    /**
     * @var string
     */
    protected $imageType;

    public function __construct(ImageHandler $imageHandler, string $imageType)
    {
        $this->imageHandler = $imageHandler;
        $this->imageType    = $imageType;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data = null)
    {
        $this->images = $data;

        if (!$data) {
            return [];
        }

        return $this->transformImages($data);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        return $this->reverseTransformImages($data);
    }

    /**
     * @param array $data
     * @return File[]
     */
    protected function transformImages(array $data)
    {
        return array_map(function ($filename) {
            return new File($filename, false);
        }, $data);
    }

    /**
     * @param array $data
     * @return null|string
     */
    protected function reverseTransformImages(array $data)
    {
        if (is_null($this->images)) {
            throw new TransformationFailedException('You must create from with entity, and only after handle request');
        }

        return array_filter(array_map(function ($file) use ($data) {
            if (is_string($file)) {
                return $file;
            }

            if (is_object($file) && UploadedFile::class === get_class($file)) {
                return $this->imageHandler->upload(
                    $file,
                    $this->imageType,
                    uniqid($data['slug'].'_')
                );
            }

            throw new UnexpectedTypeException($file, 'UploadedFile|string');

        }, array_merge($this->images, $data)));
    }
}
