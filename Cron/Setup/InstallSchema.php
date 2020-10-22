<?php
/**
 * Doppler Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Combinatoria
 * @package     Combinatoria_Doppler
 */
namespace Combinatoria\Doppler\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Combinatoria\Doppler\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create tables
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create Leadmap table
         */
        if (!$installer->tableExists('combinatoria_doppler_leadmap')) {

            $table = $installer->getConnection()
                ->newTable($installer->getTable('combinatoria_doppler_leadmap'))
                ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary'  => true, 'unsigned' => true], 'ID')
                ->addColumn('doppler_field_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Doppler field')
                ->addColumn('magento_field_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Magento field');

            $installer->getConnection()->createTable($table);
        }

        /**
         * Create DopplerLists table
         */
        if (!$installer->tableExists('combinatoria_doppler_lists')) {

            $table = $installer->getConnection()
                ->newTable($installer->getTable('combinatoria_doppler_lists'))
                ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary'  => true, 'unsigned' => true], 'ID')
                ->addColumn('list_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'List ID')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'List name')
                ->addColumn('status', Table::TYPE_SMALLINT, 1, ['nullable' => false], 'Status')
                ->addColumn('subscribers_count', Table::TYPE_INTEGER, null, ['nullable' => false], 'Subscribers count')
                ->addColumn('last_usage', Table::TYPE_TIMESTAMP, null, [], 'Last usage')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At');

            $installer->getConnection()->createTable($table);
        }

        /**
         * Create Import Tasks table
         */
        if (!$installer->tableExists('combinatoria_doppler_importtasks')) {

            $table = $installer->getConnection()
                ->newTable($installer->getTable('combinatoria_doppler_importtasks'))
                ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary'  => true, 'unsigned' => true], 'ID')
                ->addColumn('import_id', Table::TYPE_TEXT, 255, ['nullable' => false], 'Import ID')
                ->addColumn('status', Table::TYPE_SMALLINT, 1, ['nullable' => false], 'Status')
                ->addColumn('customers', Table::TYPE_TEXT, '64k', ['nullable' => false], 'Customers')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
