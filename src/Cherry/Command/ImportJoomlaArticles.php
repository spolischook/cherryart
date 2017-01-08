<?php

namespace Cherry\Command;

use Cherry\ImageHandler;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ImportJoomlaArticles extends Command
{
    /** @var ImageHandler */
    protected $imageHandler;

    /** @var  Connection */
    protected $db;

    public function __construct(ImageHandler $imageHandler, Connection $db)
    {
        $this->imageHandler = $imageHandler;
        $this->db = $db;

        parent::__construct('import:joomla_articles');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $articles = Yaml::parse(file_get_contents(__DIR__.'/../../../old_db/jos_content.yml'));
        $output->writeln(sprintf('Found "%s" articles in jos_content table dump', count($articles)));

        $articlesByType = [];

        foreach ($articles as $article) {
            if (isset($article['type'])) {
                $articlesByType[$article['type']][] = $article;
                continue;
            }

            $articlesByType['nonCatogorized'][] = $article;
        }

        $output->writeln('Articles by type:');

        foreach ($articlesByType as $type => $items) {
            $output->writeln($type.' => '.count($items));
        }

        foreach ($articlesByType['exhibition'] as $item) {
            $output->writeln($item['title']);
        }
    }
}
