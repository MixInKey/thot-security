<?php

namespace Chalasdev\Console\Command;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;

class FindAllCommand extends Command
{
    protected function configure()
    {
        $this
      ->setName('chalasdev:product:findall')
      ->setDescription('Find all orders whith In-store status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setHelperSet($this->getApplication()->getHelperSet());
        $formatter = new FormatterHelper();
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $welcome = $formatter->formatBlock('Welcome to chalasdev/doctrine-cli', 'title', true);
        $output->writeln(['', $welcome, '', 'This project provide cli-centered application to manage databases and create interactive commands, using <comment>symfony/console</comment> and <comment>doctrine/orm</comment> .', 'Created by Robin Chalas - github.com/chalasr', '']);
        $productSection = $formatter->formatSection('Products', 'ALL');
        $output->writeln([$productSection, '']);
        $em = $this->getApplication()->getHelperSet()->get('em')->getEntityManager();
        $repo = $em->getRepository('Chalasdev\Console\Entity\Product');
        $products = $repo->findAll();
        foreach ($products as $prod) {
            $output->writeln('#'.$prod->getId().'  '.$prod->getName());
        }
        $output->writeln('');
    }
}
