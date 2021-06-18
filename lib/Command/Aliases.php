<?php
declare(strict_types=1);

/**
 * @author Patrick Greyson
 *
 * Postmag - Postfix mail alias generator for Nextcloud
 * Copyright (C) 2021
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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