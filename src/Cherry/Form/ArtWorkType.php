<?php

namespace Cherry\Form;

use Cherry\ImageHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
            ])
            ->add('title_uk', TextType::class, [
                'required' => true,
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
            ->add('price', MoneyType::class, [
                'currency' => 'USD',
                'scale'    => 0,
                'grouping' => true,
                'required' => false,
            ])
            ->add('in_stock', ChoiceType::class, [
                'choices'  => [
                    'Available' => true,
                    'Sold' => false,
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

        $builder->addModelTransformer(new CallbackTransformer(
            function ($data) {
                if (null === $data) {
                    return null;
                }

                $data['picture'] = $this->transformPicture($data);
                $data['images']  = $this->transformImages($data);

                return $data;
            },
            function (array $data) {
                $data['picture'] = $this->reverceTransformPicture($data);
                $data['images']  = $this->reverceTransformImages($data);

                return $data;
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $requestData  = $event->getData();
            $normData = $event->getForm()->getNormData();

            $requestData['picture'] = $this->getNewPictureRequestData($requestData, $normData);
            $requestData['images']  = $this->getNewImagesRequestData($requestData, $normData);

            $event->setData($requestData);
        });
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
     * @return array
     */
    protected function transformImages(array $data)
    {
        if (!$data['images']) {
            return [];
        }

        return array_map(function ($filename) {
            return new File($this->imageHandler->getOriginal($filename, ImageHandler::TYPE_ART_WORK));
        }, explode(',', $data['images']));
    }

    /**
     * @param array $data
     * @return string|null
     */
    protected function reverceTransformPicture(array $data)
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

    /**
     * @param array $data
     * @return null|string
     */
    protected function reverceTransformImages(array $data)
    {
        if (!$data['images']) {
            return null;
        }

        return implode(',', array_filter(array_map(function ($file) use ($data) {
            if (null === $file) {
                return null;
            }

            if (UploadedFile::class === get_class($file)) {
                return $this->imageHandler->upload(
                    $file,
                    ImageHandler::TYPE_ART_WORK,
                    uniqid($data['slug'].'_')
                );
            }

            if (File::class === get_class($file)) {
                return $file->getFilename();
            }

            throw new UnexpectedTypeException($file, 'null|UploadedFile|File');

        }, $data['images'])));
    }
}
