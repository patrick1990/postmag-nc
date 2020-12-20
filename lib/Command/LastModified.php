<?php
declare(strict_types=1);

namespace OCA\Postmag\Command;

use Symfony\Component\Console\Command\Command;
use OCA\Postmag\Service\CommandService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class LastModified extends Command {
    
    private $service;
    
    public function __construct(CommandService $service){
        parent::__construct();
        
        $this->service = $service;
    }
    
    protected function configure() {
        $this->setName('postmag:last_modified')
            ->setDescription('Get timestamp of last alias modification')
            ->addOption(
                'formatted',
                'f',
                InputOption::VALUE_NONE,
                'Return timestamp as formatted string instead of unix time'
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln(
            $this->service->getLastModified($input->getOption('formatted'))
        );
        
        return 0;
    }
    
}