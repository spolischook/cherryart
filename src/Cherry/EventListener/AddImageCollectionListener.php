<?php

namespace Cherry\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddImageCollectionListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $requestData  = $event->getData();
        $normData = $event->getForm()->getNormData();

        $requestData['images']  = $this->getNewImagesRequestData($requestData, $normData);

        $event->setData($requestData);
    }

    /**
     * @param array $requestData
     * @param array|null $normData
     * @return array
     */
    protected function getNewImagesRequestData(array $requestData, $normData)
    {
        if (false === array_key_exists('images', $requestData) || empty($requestData['images'])) {
            return $normData['images'];
        }

        if (empty($normData['images'])) {
            return $requestData['images'];
        }

        return array_merge($normData['images'], $requestData['images']);
    }
}
