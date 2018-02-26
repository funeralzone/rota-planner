<?php

namespace ChrisHarrison\RotaPlanner\Commands;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DailyCommand extends Command
{
    protected function configure()
    {
        $this->setName('daily');
        $this->setDescription('Run all the commands for the day');
        $this->addArgument('date', InputArgument::OPTIONAL, 'The day to run the command for. Defaults to today.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $whenInput = $input->getArgument('date') ?: getenv('DATE_OVERRIDE') ?: Carbon::now()->format('Y-m-d');
        $when = Carbon::createFromFormat('Y-m-d', $whenInput);

        $syncMembers = $this->getApplication()->find('sync:members');
        $syncMembers->run(new ArrayInput([
            'command' => 'sync:members'
        ]), $output);

        $build = $this->getApplication()->find('build');
        $build->run(new ArrayInput([
            'command' => 'build',
            'date' => $when->format('Y-m-d')
        ]), $output);

        $build = $this->getApplication()->find('remind');
        $build->run(new ArrayInput([
            'command' => 'remind',
            'date' => $when->format('Y-m-d')
        ]), $output);
    }
}
