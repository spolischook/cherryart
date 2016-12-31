<?php

namespace Cherry\Form\DataTransformer;

use Cherry\ImageHandler;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageTransformer implements DataTransformerInterface
{
    /**
     * @var ImageHandler
     */
    protected $imageHandler;

    public function __construct(ImageHandler $imageHandler)
    {
        $this->imageHandler = $imageHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        if (null === $data) {
            return null;
        }

        $data['picture'] = $this->transformPicture($data);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        $data['picture'] = $this->reverSeTransformPicture($data);

        return $data;
    }

    /**
     * @param array $data
     * @return File|null
     */
    protected function transformPicture(array $data)
    {
        if (!$data['picture']) {
            return null;
        }

        return new File($this->imageHandler->getOriginal($data['picture'], ImageHandler::TYPE_ART_WORK));
    }

    /**
     * @param array $data
     * @return string|null
     */
    protected function reverSeTransformPicture(array $data)
    {
        if (!$data['picture']) {
            return null;
        }

        if (UploadedFile::class === get_class($data['picture'])) {
            return $this->imageHandler->upload(
                $data['picture'],
                ImageHandler::TYPE_ART_WORK,
                $data['slug']
            );
        }

        if (File::class === get_class($data['picture'])) {
            return $data['picture']->getFilename();
        }

        throw new UnexpectedTypeException($data['picture'], 'null|UploadedFile|File');
    }
}
