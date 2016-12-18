<?php

namespace Cherry\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ArtWorkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('slug', TextType::class)
            ->add('description', TextareaType::class)
            ->add('price', CurrencyType::class)
            ->add('inStock', ChoiceType::class, [
                'choices'  => [
                    'Available' => true,
                    'Sold' => false,
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('picture', FileType::class, [
                'required' => true,
            ])
        ;
    }
}
