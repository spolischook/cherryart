<?php

namespace Cherry\Form;

use Cherry\EventListener\AddImageCollectionListener;
use Cherry\EventListener\AddImageListener;
use Cherry\EventListener\UpdateUnixTimeListener;
use Cherry\Form\DataTransformer\ArtWorksTransformer;
use Cherry\Form\DataTransformer\ImageCollectionTransformer;
use Cherry\Form\DataTransformer\ImageTransformer;
use Cherry\ImageHandler;
use Cherry\Repository\NewsRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class NewsExhibitionType extends AbstractType
{
    /** @var ImageHandler */
    protected $imageHandler;

    /** @var  Connection */
    protected $newsRepository;

    public function __construct(ImageHandler $imageHandler, NewsRepository $newsRepository)
    {
        $this->imageHandler = $imageHandler;
        $this->newsRepository = $newsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = new \DateTime();
        $builder
            ->add('title_en', TextType::class, [
                'required' => true,
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('title_uk', TextType::class, [
                'required' => true,
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('text_en', TextareaType::class, [
                'required' => false,
            ])
            ->add('text_uk', TextareaType::class, [
                'required' => false,
            ])
            ->add('date', TextType::class, [
                'constraints' => [new Assert\Regex([
                    'pattern' => '/\d{4}-\d{2}-\d{2}/',
                    'message' => 'The data must be in format YYYY-MM-DD',
                ])],
                'empty_data' => $today->format('Y-m-d'),
                'required' => false,
            ])
            ->add('date_unix', HiddenType::class)
            ->add('type', HiddenType::class, [
                'empty_data' => 'exhibition',
            ])
            ->add('picture', FileType::class, [
                'required' => false,
            ])
            ->add('images', FileType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('art_works', HiddenType::class, [
                'required' => false,
            ])
        ;

        $builder->addModelTransformer(new ImageTransformer($this->imageHandler, ImageHandler::TYPE_NEWS));
        $builder->addModelTransformer(new ImageCollectionTransformer($this->imageHandler, ImageHandler::TYPE_NEWS));
        $builder->addModelTransformer(new ArtWorksTransformer($this->newsRepository));
        $builder->addEventSubscriber(new AddImageListener());
        $builder->addEventSubscriber(new AddImageCollectionListener());
        $builder->addEventSubscriber(new UpdateUnixTimeListener());
    }
}
