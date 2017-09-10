<?php

namespace Cherry\Tests;

use Cherry\ImageHandler;
use Intervention\Image\Image;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageHandlerTest extends \PHPUnit_Framework_TestCase
{
    public static $originalPath = 'cherryArt/originalPath';
    public static $thumbnailPath = 'cherryArt/thumbnailPath';
    public static $webThumbnailPath = '/assets/web/images';
    public static $sourceTempFile = __DIR__.'/data/test-image.jpg';
    public static $tempUploadedFile = null;
    public static $formats = [];

    /** @var  ImageHandler */
    public $imageHandler;

    public static function setUpBeforeClass()
    {
        self::$originalPath = sys_get_temp_dir().'/'.self::$originalPath;
        self::$thumbnailPath = sys_get_temp_dir().'/'.self::$thumbnailPath;
        self::$tempUploadedFile = sys_get_temp_dir().'/test-image.jpg';
        mkdir(self::$originalPath, 0777, true);
        mkdir(self::$thumbnailPath, 0777, true);
        self::$formats = [
            'admin' => function (Image $image) {
                return $image->heighten(70)->crop(70, 70);
            },
            'admin_preview' => function (Image $image) {
                return $image->heighten(150)->crop(150, 150);
            },
        ];
    }

    public function setUp()
    {
        copy(self::$sourceTempFile, self::$tempUploadedFile);
        $this->imageHandler = new ImageHandler(
            self::$originalPath,
            self::$thumbnailPath,
            self::$webThumbnailPath,
            self::$formats
        );
    }

    public function testUpload()
    {
        $file = new UploadedFile(self::$tempUploadedFile, 'test-image.jpg', 'image/jpeg', null, null, true);
        $this->imageHandler->upload($file, 'news', 'awesome-exhibition');

        $this->assertTrue(is_file(sys_get_temp_dir().'/cherryArt/originalPath/news/awesome-exhibition.jpeg'));
        $this->assertTrue(is_file(sys_get_temp_dir().'/cherryArt/thumbnailPath/admin/news/awesome-exhibition.jpeg'));
        $this->assertTrue(is_file(sys_get_temp_dir().'/cherryArt/thumbnailPath/admin_preview/news/awesome-exhibition.jpeg'));
    }

    /**
     * @depends testUpload
     */
    public function testGetUrl()
    {
        $imageUrl = $this->imageHandler->getImageUrl('news/awesome-exhibition.jpeg', 'admin');
        $this->assertEquals('/assets/web/images/admin/news/awesome-exhibition.jpeg', $imageUrl);
    }

    /**
     * @depends testUpload
     */
    public function testGetOriginal()
    {
        $originalImagePath = $this->imageHandler->getOriginal('news/awesome-exhibition.jpeg');
        $this->assertEquals(sys_get_temp_dir().'/cherryArt/originalPath/news/awesome-exhibition.jpeg', $originalImagePath);
    }

    public function testGetThumbnail()
    {
        $thumbnailPath = $this->imageHandler->getThumbnail('news/awesome-exhibition.jpeg', 'admin_preview');
        $this->assertEquals(sys_get_temp_dir().'/cherryArt/thumbnailPath/admin_preview/news/awesome-exhibition.jpeg', $thumbnailPath);
    }

    public function testGetOriginalPath()
    {
        $this->assertEquals(sys_get_temp_dir().'/cherryArt/originalPath', $this->imageHandler->getOriginalPath());
    }

    /**
     * @expectedException \Intervention\Image\Exception\NotFoundException
     * @expectedExceptionMessage Original image path "/etc/badOriginalPath" not found
     */
    public function testBadOriginalPath()
    {
        new ImageHandler(
            '/etc/badOriginalPath',
            self::$thumbnailPath,
            self::$webThumbnailPath,
            self::$formats
        );
    }

    /**
     * @expectedException \Intervention\Image\Exception\NotFoundException
     * @expectedExceptionMessage Thumbnail image path "/etc/bad/thumbnailPath" not found
     */
    public function testBadThumbnailPath()
    {
        new ImageHandler(
            self::$originalPath,
            '/etc/bad/thumbnailPath',
            self::$webThumbnailPath,
            self::$formats
        );
    }

    public static function tearDownAfterClass()
    {
        $filesystem = new Filesystem();

        $filesystem->remove(self::$originalPath);
        $filesystem->remove(self::$thumbnailPath);
        $filesystem->remove(self::$webThumbnailPath);
    }
}
