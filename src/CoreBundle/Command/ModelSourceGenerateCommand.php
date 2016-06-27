<?php

namespace AppGear\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Generate entities
 */
class ModelSourceGenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('appgear:core:model:source_generate')
            ->setDescription('Generate source code for models')
            ->addArgument('name', InputArgument::OPTIONAL, 'Generate source only for the model');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelManager    = $this->getContainer()->get('app_gear.core.model.manager');
        $sourceGenerator = $this->getContainer()->get('app_gear.core.model.source_generator');

        if ($name = $input->getArgument('name')) {
            $sourceGenerator->generate($modelManager->get($name));
        } else {
            foreach ($modelManager->all() as $model) {
                $output->writeln('Build ' . $model->getName());
                $sourceGenerator->generate($model);
            }
        }

        $output->write('Complete!', true);
    }
}