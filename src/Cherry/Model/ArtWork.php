<?php

namespace Cherry\Model;

class ArtWork extends ActiveRecord
{
    protected $titleEn;

    protected $titleUk;

    protected $descriptionEn;

    protected $descriptionUk;

    protected $slug;

    protected $price;

    protected $inStock;

    protected $onFront;

    protected $picture;

    protected $images = [];

    protected $width;

    protected $height;

    protected $date;

    protected $dateUnix;

    protected $materialsEn;

    protected $materialsUk;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->dateUnix = $this->date->format('U');
    }

    /** {@inheritdoc} */
    public static function getTable()
    {
        return 'art_works';
    }

    public static function beforeMap(array &$values)
    {
        $values['date'] = new \DateTime($values['date']);
        $values['images'] = is_string($values['images']) ? explode(',', $values['images']) : [];
    }

    /**
     * @return mixed
     */
    public function getTitleEn()
    {
        return $this->titleEn;
    }

    /**
     * @param mixed $titleEn
     * @return ArtWork
     */
    public function setTitleEn($titleEn)
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitleUk()
    {
        return $this->titleUk;
    }

    /**
     * @param mixed $titleUk
     * @return ArtWork
     */
    public function setTitleUk($titleUk)
    {
        $this->titleUk = $titleUk;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * @param mixed $descriptionEn
     * @return ArtWork
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescriptionUk()
    {
        return $this->descriptionUk;
    }

    /**
     * @param mixed $descriptionUk
     * @return ArtWork
     */
    public function setDescriptionUk($descriptionUk)
    {
        $this->descriptionUk = $descriptionUk;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     * @return ArtWork
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return ArtWork
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInStock()
    {
        return $this->inStock;
    }

    /**
     * @param mixed $inStock
     * @return ArtWork
     */
    public function setInStock($inStock)
    {
        $this->inStock = $inStock;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnFront()
    {
        return $this->onFront;
    }

    /**
     * @param bool $isOrNot
     * @return $this
     */
    public function setOnFront(bool $isOrNot)
    {
        $this->onFront = $isOrNot;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     * @return ArtWork
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param array $images
     * @return ArtWork
     */
    public function setImages(array $images): ArtWork
    {
        $this->images = $images;
        return $this;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param integer $width
     * @return ArtWork
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param integer $height
     * @return ArtWork
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return ArtWork
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateUnix()
    {
        return $this->dateUnix;
    }

    /**
     * @param mixed $dateUnix
     * @return ArtWork
     */
    public function setDateUnix($dateUnix)
    {
        $this->dateUnix = $dateUnix;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaterialsEn()
    {
        return $this->materialsEn;
    }

    /**
     * @param mixed $materialsEn
     * @return ArtWork
     */
    public function setMaterialsEn($materialsEn)
    {
        $this->materialsEn = $materialsEn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaterialsUk()
    {
        return $this->materialsUk;
    }

    /**
     * @param mixed $materialsUk
     * @return ArtWork
     */
    public function setMaterialsUk($materialsUk)
    {
        $this->materialsUk = $materialsUk;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize():array
    {
        $array = [
            'title_en' => $this->getTitleEn(),
            'title_uk' => $this->getTitleUk(),
            'picture' => $this->getPicture(),
            'slug' => $this->getSlug(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'price' => $this->getPrice(),
            'date' => $this->getDate()->format('Y-m-d'),
            'date_unix' => $this->getDateUnix(),
            'in_stock' => $this->getInStock() ? 1 : 0,
            'on_front' => $this->isOnFront() ? 1 : 0,
        ];

        if ($this->getId()) {
            $array['id'] = $this->getId();
        }

        return $array;
    }
}
