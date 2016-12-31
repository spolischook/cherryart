<?php

namespace Cherry\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddImageListener implements EventSubscriberInterface
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

        if (!array_key_exists('picture', $requestData)) {
            throw new UnprocessableEntityHttpException(sprintf('"%s" can not be applied to entity', self::class));
        }

        $requestData['picture'] = $this->getNewPictureRequestData($requestData, $normData);
        $event->setData($requestData);
    }

    /**
     * @param array $requestData
     * @param array|null $normData
     * @return UploadedFile|File|null
     */
    protected function getNewPictureRequestData(array $requestData, $normData)
    {
        if ($normData['picture'] !== null && $requestData['picture'] === null) {
            return $normData['picture'];
        }

        return $requestData['picture'];
    }
}
