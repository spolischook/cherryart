<?php

namespace Cherry\Model;

use Cherry\Application;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class News extends ActiveRecord
{
    const ORDER_BY = 'date_unix DESC';

    /** @var string */
    protected $id;

    /** @var  string */
    protected $titleEn = '';

    /** @var  string */
    protected $titleUk = '';

    /** @var  string */
    protected $slug = '';

    /** @var  string */
    protected $type = '';

    /** @var  string */
    protected $picture = '';

    /** @var  array */
    protected $images = [];

    /** @var  string */
    protected $textEn = '';

    /** @var  string */
    protected $textUk = '';

    /** @var  \DateTime */
    protected $date;

    /** @var  int */
    protected $dateUnix;

    /** @var  ArtWork[] */
    protected $artWorks = [];

    public function __construct()
    {
        $this->id = uniqid();
        $this->date = new \DateTime();
        $this->dateUnix = $this->date->format('U');
    }

    public static function getTable()
    {
        return 'news';
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitleEn(): string
    {
        return $this->titleEn;
    }

    /**
     * @param string $titleEn
     */
    public function setTitleEn(string $titleEn)
    {
        $this->titleEn = $titleEn;
    }

    /**
     * @return string
     */
    public function getTitleUk(): string
    {
        return $this->titleUk;
    }

    /**
     * @param string $titleUk
     */
    public function setTitleUk(string $titleUk)
    {
        $this->titleUk = $titleUk;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
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
     */
    public function setImages(array $images)
    {
        $this->images = $images;
    }

    /**
     * @return string
     */
    public function getTextEn(): ?string
    {
        return $this->textEn;
    }

    /**
     * @param string $textEn
     */
    public function setTextEn(string $textEn = null)
    {
        $this->textEn = $textEn;
    }

    /**
     * @return string
     */
    public function getTextUk(): ?string
    {
        return $this->textUk;
    }

    /**
     * @param string $textUk
     */
    public function setTextUk(string $textUk = null)
    {
        $this->textUk = $textUk;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getDateUnix(): int
    {
        return $this->dateUnix;
    }

    /**
     * @param int $dateUnix
     */
    public function setDateUnix(int $dateUnix)
    {
        $this->dateUnix = $dateUnix;
    }

    /**
     * @return ArtWork[]
     */
    public function getArtWorks(): array
    {
        $sql = "SELECT * FROM `news_art_works`CROSS JOIN art_works on news_art_works.art_works_id = art_works.id  WHERE news_id = ? ";

        $result = Application::$db->fetchAll($sql, [$this->getId()]);
        // Remove undefined fields in ArtWork
        array_walk($result, function (array &$item) {
            unset($item['news_id']);
            unset($item['art_works_id']);
        });

        return array_map([ArtWork::class, 'map'], $result);
    }

    /**
     * @param ArtWork[] $artWorks
     */
    public function setArtWorks(array $artWorks)
    {
        $this->artWorks = $artWorks;
    }

    public static function beforeMap(array &$values)
    {
        $values['date'] = new \DateTime($values['date']);
        $values['images'] = explode(',', $values['images']);
    }
}
