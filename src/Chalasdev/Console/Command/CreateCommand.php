<?php

namespace Chalasdev\Console\Command;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Chalasdev\Console\Entity;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this
      ->setName('chalasdev:product:create')
      ->setDescription('Find all orders whith In-store status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setHelperSet($this->getApplication()->getHelperSet());
        $em = $this->getApplication()->getHelperSet()->get('em')->getEntityManager();
        $formatter = new FormatterHelper();
        $questionHelper = new QuestionHelper();
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $welcome = $formatter->formatBlock('Welcome to chalasdev/doctrine-cli', 'title', true);
        $output->writeln(['', $welcome, '', 'This project provide cli-centered application to manage databases and create interactive commands, using <comment>symfony/console</comment> and <comment>doctrine/orm</comment> .', 'Created by Robin Chalas - github.com/chalasr', '']);
        $question = new Question("Hi! What's the name of product your want create ?");
        $name = $questionHelper->ask($input, $output, $question);
        $product = new Entity\Product();
        $product->setName($name);
        $em->persist($product);
        $em->flush();
        $output->writeln(['', '<comment>New project successfully created</comment> .', 'Created by Robin Chalas - github.com/chalasr']);
    }
}
