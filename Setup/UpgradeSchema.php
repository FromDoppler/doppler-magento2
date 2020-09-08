<?php
namespace Combinatoria\Doppler\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;

        $installer->startSetup();

        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            if (!$installer->tableExists('combinatoria_doppler_mapcustomers')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('combinatoria_doppler_mapcustomers'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary'  => true, 'unsigned' => true], 'ID')
                    ->addColumn('doppler_field_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Doppler field')
                    ->addColumn('magento_field_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Magento field');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('combinatoria_doppler_mapsubscribers')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('combinatoria_doppler_mapsubscribers'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary'  => true, 'unsigned' => true], 'ID')
                    ->addColumn('doppler_field_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Doppler field')
                    ->addColumn('magento_field_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Magento field');

                $installer->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }
}