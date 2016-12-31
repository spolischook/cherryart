<?php

namespace Cherry\Form;

use Cherry\EventListener\AddImageCollectionListener;
use Cherry\EventListener\AddImageListener;
use Cherry\EventListener\UpdateUnixTimeListener;
use Cherry\Form\DataTransformer\ImageCollectionTransformer;
use Cherry\Form\DataTransformer\ImageTransformer;
use Cherry\ImageHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ArtWorkType extends AbstractType
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
            ->add('description_en', TextareaType::class, [
                'required' => false,
            ])
            ->add('description_uk', TextareaType::class, [
                'required' => false,
            ])
            ->add('materials_en', TextType::class, [
                'required' => false,
            ])
            ->add('materials_uk', TextType::class, [
                'required' => false,
            ])
            ->add('width', IntegerType::class, [
                'constraints' => [new Assert\Type(['type' => 'integer'])],
            ])
            ->add('height', IntegerType::class, [
                'constraints' => [new Assert\Type(['type' => 'integer'])],
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
            ->add('price', MoneyType::class, [
                'currency' => 'USD',
                'scale'    => 0,
                'grouping' => true,
                'required' => false,
            ])
            ->add('in_stock', ChoiceType::class, [
                'choices'  => [
                    'Available' => 1,
                    'Sold' => 0,
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('picture', FileType::class, [
                'required' => false,
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => FileType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
            ])
        ;

        $builder->addModelTransformer(new ImageTransformer($this->imageHandler));
        $builder->addModelTransformer(new ImageCollectionTransformer($this->imageHandler));
        $builder->addEventSubscriber(new AddImageListener());
        $builder->addEventSubscriber(new AddImageCollectionListener());
        $builder->addEventSubscriber(new UpdateUnixTimeListener());
    }
}
