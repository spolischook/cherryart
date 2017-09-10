<?php

namespace Cherry\Form;

use Cherry\Form\DataTransformer\PictureTransformer;
use Cherry\ImageHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureType extends AbstractType
{
    protected $imageHandler;

    public function __construct(ImageHandler $imageHandler)
    {
        $this->imageHandler = $imageHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileName', HiddenType::class)
            ->add('file', FileType::class, array(
                'label' => false
            ))
            ->addModelTransformer(new PictureTransformer())
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($options) {
                $data = $event->getData();
                $form = $event->getForm();
                $slug = $form->getParent()->get('slug')->getData();

                if ($data['file'] instanceof UploadedFile) {
                    $data['fileName'] = $this->imageHandler->upload($data['file'], $options['subdirectory'], $slug);
                }

                $event->setData($data);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('subdirectory');
        $resolver->setRequired(['subdirectory']);
    }
}
