<?php
declare(strict_types=1);

namespace OCA\Postmag\Command;

use Symfony\Component\Console\Command\Command;
use OCA\Postmag\Service\CommandService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Aliases extends Command {
    
    private $service;
    
    public function __construct(CommandService $service){
        parent::__construct();
        
        $this->service = $service;
    }
    
    protected function configure() {
        $this->setName('postmag:aliases')
            ->setDescription('Generate alias file');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln($this->service->formatPostfixAliasFile());
        
        return 0;
    }
    
}