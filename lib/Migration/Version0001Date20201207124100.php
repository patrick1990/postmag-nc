<?php
namespace OCA\Postmag\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0001Date20201207124100 extends SimpleMigrationStep {
    
    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
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
                'length' => 10,
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
                'length' => 10
            ]);
            $table->addColumn('alias_name', 'string', [
                'notnull' => true,
                'length' => 20
            ]);
            $table->addColumn('comment', 'string', [
                'notnull' => true,
                'default' => '',
                'length' => 40
            ]);
            $table->addColumn('enabled', 'boolean', [
                'notnull' => true,
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