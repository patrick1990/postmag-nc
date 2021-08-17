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

namespace OCA\Postmag\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;
use OCA\Postmag\Service\ConfigService;

class Version0001Date20201207124100 extends SimpleMigrationStep {
    
    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        
        if (!$schema->hasTable('postmag_user')) {
            $table = $schema->createTable('postmag_user');
            
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64
            ]);
            $table->addColumn('user_alias_id', 'string', [
                'notnull' => true,
                'length' => ConfigService::MAX_USER_ALIAS_ID_LEN,
                'customSchemaOptions' => [
                    'unique' => true
                ]
            ]);
            
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'postmag_user_user_id_index');
        }
        
        if (!$schema->hasTable('postmag_alias')) {
            $table = $schema->createTable('postmag_alias');
            
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64
            ]);
            $table->addColumn('alias_id', 'string', [
                'notnull' => true,
                'length' => ConfigService::MAX_ALIAS_ID_LEN
            ]);
            $table->addColumn('alias_name', 'string', [
                'notnull' => true,
                'length' => ConfigService::MAX_ALIAS_NAME_LEN
            ]);
            $table->addColumn('to_mail', 'string', [
                'notnull' => true,
                'length' => ConfigService::MAX_TO_MAIL_LEN
            ]);
            $table->addColumn('comment', 'string', [
                'notnull' => true,
                'default' => '',
                'length' => ConfigService::MAX_COMMENT_LEN
            ]);
            $table->addColumn('enabled', 'boolean', [
                'notnull' => false,
                'default' => true
            ]);
            if (PHP_INT_SIZE >= 8) {
                $table->addColumn('created', 'bigint', [
                    'notnull' => true
                ]);
                $table->addColumn('last_modified', 'bigint', [
                    'notnull' => true
                ]);
            }
            else {
                $table->addColumn('created', 'integer', [
                    'notnull' => true
                ]);
                $table->addColumn('last_modified', 'integer', [
                    'notnull' => true
                ]);
            }
            
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'postmag_alias_user_id_index');
            $table->addUniqueIndex(['alias_id', 'alias_name', 'user_id'], 'postmag_alias_name_index');
        }
        
        return $schema;
    }
    
}