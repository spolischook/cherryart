<?php

namespace Cherry\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UpdateUnixTimeListener implements EventSubscriberInterface
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

        try {
            $date = new \DateTime($requestData['date']);
        } catch (\Exception $e) {
            //validation for date field should turn back this request
            return;
        }

        $requestData['date_unix'] = $date->format('U');
        $event->setData($requestData);
    }
}
