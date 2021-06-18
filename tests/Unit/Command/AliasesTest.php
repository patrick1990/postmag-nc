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

namespace OCA\Postmag\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use OCA\Postmag\Service\CommandService;
use OCA\Postmag\Command\Aliases;
use Symfony\Component\Console\Input\InputOption;

class AliasesTest extends TestCase {
    
    private const REQUIRED_ARGS = [];
    private const OPTIONAL_ARGS = [];
    private const OPTS = [];
    
    private $command;
    private $service;
    
    public function setUp(): void {
        $this->service = $this->createMock(CommandService::class);
        
        $this->command = new Aliases($this->service);
    }
    
    public function testName() {
        $this->assertSame(
            'postmag:aliases',
            $this->command->getName(),
            'aliases command has the wrong name.'
        );
    }
    
    public function testDescription() {
        $this->assertSame(
            'Generate alias file',
            $this->command->getDescription(),
            'aliases command has the wrong description.'
        );
    }
    
    public function testArguments() {
        $arguments = $this->command->getDefinition()->getArguments();
        
        foreach ($arguments as $arg) {
            if($arg->isRequired()) {
                $this->assertTrue(
                    in_array($arg->getName(), self::REQUIRED_ARGS),
                    $arg->getName().' is not a required argument.'
                );
            }
            else {
                $this->assertTrue(
                    in_array($arg->getName(), self::OPTIONAL_ARGS),
                    $arg->getName().' is not an optional argument.'
                );
            }
        }
        
        // Prevent test of beeing useless in case of no argument
        $this->assertTrue(true);
    }
    
    public function testOptions() {
        $options = $this->command->getDefinition()->getOptions();
        
        foreach ($options as $opt) {
            if (!array_key_exists($opt->getName(), self::OPTS)) {
                $this->assertTrue(false, $opt->getName().' is not an option.');
            }
            
            switch (self::OPTS[$opt->getName()]) {
                case InputOption::VALUE_NONE:
                    $this->assertTrue(!$opt->acceptValue(), $opt->getName().' accepts a value.');
                    break;
                case InputOption::VALUE_OPTIONAL:
                    $this->assertTrue($opt->isValueOptional(), $opt->getName().' has no optional value.');
                    break;
                case InputOption::VALUE_REQUIRED:
                    $this->assertTrue($opt->isValueRequired(), $opt->getName().' has no required value.');
                    break;
                case InputOption::VALUE_IS_ARRAY:
                    $this->assertTrue($opt->isArray(), $opt->getName().' is not an array.');
            }
        }
        
        // Prevent test of beeing useless in case of no argument
        $this->assertTrue(true);
    }
    
}