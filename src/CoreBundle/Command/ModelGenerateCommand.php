<?php

namespace AppGear\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Generate entities
 */
class ModelGenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('appgear:core:model:generate')
            ->setDescription('Generate source code for models')
            ->addArgument('name', InputArgument::OPTIONAL, 'Generate source only for the model')
            ->addOption('new', null, InputOption::VALUE_NONE, 'Use new model generator')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelManager    = $this->getContainer()->get('app_gear.core.model.manager');

        if ($input->getOption('new')) {
            $sourceGenerator = $this->getContainer()->get('app_gear.core.model.generator');
        } else {
            $sourceGenerator = $this->getContainer()->get('app_gear.core.model.generator.source');
        }

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