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

class AddUnixTime extends Command
{
    /** @var ImageHandler */
    protected $imageHandler;

    /** @var  Connection */
    protected $db;

    public function __construct(ImageHandler $imageHandler, Connection $db)
    {
        $this->imageHandler = $imageHandler;
        $this->db = $db;

        parent::__construct('fix-pictures-path');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $array = $this->db->fetchAll('SELECT * FROM `art_works` ORDER BY `date_unix` ASC');
        $progress = new ProgressBar($output, count($array));
        $progress->start();

        foreach ($array as $data) {
            $sql = "UPDATE art_works SET picture = ? WHERE id = ?";
            $this->db->executeUpdate($sql, ['art_work/'.$data['picture'], $data['id']]);
            $progress->advance();
        }

        $progress->finish();
    }
}
