<?php

namespace Cherry\Tests\Model;

use Cherry\Application;
use Cherry\Model\News;

class NewsTest extends \PHPUnit_Framework_TestCase
{
    public function testFind()
    {
        Application::$db = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        Application::$db->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn([
                'id' => 5,
                'titleEn' => 'Hello World!',
                'titleUk' => 'This is uk string',
                'slug' => 'unique-news-item-slug',
                'type' => 'exhibition',
                'picture' => 'main-picture.jpg',
                'textEn' => 'Text english',
                'textUk' => 'Text ukrainian',
                'date' => '2016-08-24',
                'dateUnix' => 343434343,
                'images' => 'temp.jpg, temp2.jpg, temp3.jpg',
            ]);
        /** @var News $item */
        $item = News::find(3);

        $this->assertInstanceOf('Cherry\Model\News', $item);
        $this->assertInstanceOf('\DateTime', $item->getDate());

        $this->assertInternalType('array', $item->getImages());
        $this->assertCount(3, $item->getImages());

        $this->assertEquals(5, $item->getId());
        $this->assertEquals('Hello World!', $item->getTitleEn());
        $this->assertEquals('This is uk string', $item->getTitleUk());
        $this->assertEquals('unique-news-item-slug', $item->getSlug());
        $this->assertEquals('exhibition', $item->getType());
        $this->assertEquals('main-picture.jpg', $item->getPicture());
        $this->assertEquals('Text english', $item->getTextEn());
        $this->assertEquals('Text ukrainian', $item->getTextUk());
        $this->assertEquals(343434343, $item->getDateUnix());
    }

    public function testFindAll()
    {
        Application::$db = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        Application::$db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 5,
                    'date' => '2016-08-24',
                    'images' => 'temp.jpg, temp2.jpg, temp3.jpg',
                ],
                [
                    'id' => 6,
                    'date' => '2016-09-01',
                    'images' => 'temp4.jpg, temp5.jpg',
                ]
            ]);
        /** @var News[] $items */
        $items = News::findAll();

        $this->assertInternalType('array', $items);
        $this->assertCount(2, $items);

        $firstItem = array_shift($items);
        $this->assertInstanceOf('Cherry\Model\News', $firstItem);
        $this->assertInstanceOf('\DateTime', $firstItem->getDate());
        $this->assertInternalType('array', $firstItem->getImages());
        $this->assertCount(3, $firstItem->getImages());
    }

    public function testGetArtWorks()
    {
        Application::$db = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        Application::$db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 5,
                    'date' => '2016-08-24',
                    'images' => 'temp.jpg, temp2.jpg, temp3.jpg',
                ],
                [
                    'id' => 6,
                    'date' => '2016-09-01',
                    'images' => 'temp4.jpg, temp5.jpg',
                ]
            ]);

        $newsItem = new News();
        $artWorks = $newsItem->getArtWorks();

        $this->assertInternalType('array', $artWorks);

        $artWork = array_shift($artWorks);

        $this->assertInstanceOf('Cherry\Model\ArtWork', $artWork);
        $this->assertEquals(5, $artWork->getId());
    }
}
