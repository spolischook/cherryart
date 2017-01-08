<?php

namespace Cherry\Form\DataTransformer;

use Cherry\Repository\NewsRepository;
use Symfony\Component\Form\DataTransformerInterface;

class ArtWorksTransformer implements DataTransformerInterface
{
    /** @var  NewsRepository */
    protected $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        if (null === $data) {
            return null;
        }

        if (!isset($data['id'])) {
            return null;
        }

        $data['art_works'] = json_encode($this->newsRepository->findArtWorks($data['id']));

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        if (null === $data) {
            return null;
        }

        $artWorks = json_decode($data['art_works']);
        $this->newsRepository->setArtWorks($data['id'], $artWorks);

        unset($data['art_works']);

        return $data;
    }
}
