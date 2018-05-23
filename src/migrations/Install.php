<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis\migrations;

use superbig\valassis\Valassis;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    public $couponTable   = '{{%valassis_coupons}}';
    public $customerTable = '{{%valassis_customers}}';
    public $importTable   = '{{%valassis_imports}}';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema($this->couponTable);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                $this->couponTable,
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    'siteId'      => $this->integer()->notNull(),
                    'customerId'  => $this->integer()->null()->defaultValue(null),
                    'importId'    => $this->integer()->null()->defaultValue(null),
                    'couponPin'   => $this->string(255)->notNull()->defaultValue(''),
                    'consumerId'  => $this->string(255)->notNull()->defaultValue(''),
                    'response'    => $this->text()->defaultValue(null),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema($this->customerTable);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                $this->customerTable,
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    'siteId'      => $this->integer()->notNull(),
                    'name'        => $this->string(255)->notNull()->defaultValue(''),
                    'email'       => $this->string(255)->notNull()->defaultValue(''),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema($this->importTable);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                $this->importTable,
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    'siteId'      => $this->integer()->notNull(),
                    'payload'     => $this->text()->null()->defaultValue(null),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                $this->couponTable,
                'importId',
                false
            ),
            $this->couponTable,
            'importId',
            false
        );

        $this->createIndex(
            $this->db->getIndexName(
                $this->customerTable,
                'email',
                false
            ),
            $this->customerTable,
            'email',
            false
        );

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName($this->couponTable, 'siteId'),
            $this->couponTable,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName($this->couponTable, 'customerId'),
            $this->couponTable,
            'customerId',
            $this->customerTable,
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName($this->couponTable, 'importId'),
            $this->couponTable,
            'importId',
            $this->importTable,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName($this->customerTable, 'siteId'),
            $this->customerTable,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName($this->importTable, 'siteId'),
            $this->importTable,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists($this->couponTable);

        $this->dropTableIfExists($this->customerTable);

        $this->dropTableIfExists($this->importTable);
    }
}
