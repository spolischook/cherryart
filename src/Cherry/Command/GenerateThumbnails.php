<?php

namespace Cherry\Command;

use Cherry\ImageHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateThumbnails extends Command
{
    /** @var ImageHandler */
    protected $imageHandler;

    public function __construct(ImageHandler $imageHandler)
    {
        $this->imageHandler = $imageHandler;

        parent::__construct('generate-thumbnails');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start generating thumbnails');
        $numberThumbnailGenerated = 0;

        foreach (scandir($this->imageHandler->getOriginalPath()) as $type) {
            if (in_array($type, ['.', '..'])) {
                continue;
            }

            if (!is_dir($this->imageHandler->getOriginalPath().'/'.$type)) {
                continue;
            }

            foreach (scandir($this->imageHandler->getOriginalPath() . '/' . $type) as $item) {
                $file = $this->imageHandler->getOriginalPath().'/'.$type.'/'.$item;

                if (in_array($item, ['.', '..'])) {
                    continue;
                }

                if (!is_file($file)) {
                    continue;
                }

                $this->imageHandler->createThumbnails($item, $type);
                $numberThumbnailGenerated++;
            }
        }

        $output->writeln(sprintf('%s thumbnails was generated', $numberThumbnailGenerated));
    }
}
