<?php

namespace Cherry\Command;

use Cherry\ImageHandler;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ImportJomGallery extends Command
{
    /** @var ImageHandler */
    protected $imageHandler;

    /** @var  Connection */
    protected $db;

    public function __construct(ImageHandler $imageHandler, Connection $db)
    {
        $this->imageHandler = $imageHandler;
        $this->db = $db;

        parent::__construct('import:jom_gallery');
    }

    protected function configure()
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED, 'Yml file exported from jos_joomgallery table')
            ->addArgument('originals', InputArgument::REQUIRED, 'Directory with originals of images')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $array = Yaml::parse(file_get_contents($filename));
        $progress = new ProgressBar($output, count($array));
        $progress->start();

        foreach ($array as $data) {
            $progress->setMessage(sprintf('Process "%s" image...', $data['imgtitle']));

            $date = new \DateTime($data['imgdate']);
            $sourceFile = $input->getArgument('originals').'/'.$data['imgfilename'];

            $artWork = [
                'title_uk' => $data['imgtitle'],
                'title_en' => $data['imgtitle'],
                'slug'     => $data['alias'],
                'date'     => $date->format('Y-m-d'),
                'materials_en' => isset($data['material']) ? $data['material'] : null,
                'materials_uk' => isset($data['material']) ? $data['material'] : null,
                'width'    => isset($data['width']) ? $data['width'] : null,
                'height'   => isset($data['height']) ? $data['height'] : null,
                'in_stock' => $data['availability'],
                'price'    => isset($data['price']) ? $data['price'] : null,
                'picture'  => $data['alias'].'.'.pathinfo($sourceFile, PATHINFO_EXTENSION),
            ];

            $newOrigin  = $this->imageHandler->getOriginalPath().'/'.ImageHandler::TYPE_ART_WORK.'/'.$artWork['picture'];
            copy($sourceFile, $newOrigin);

            $this->imageHandler->createThumbnails($artWork['picture'], ImageHandler::TYPE_ART_WORK);

            $this->db->insert('art_works', $artWork);

            $progress->advance();
        }

        $progress->finish();
    }
}
