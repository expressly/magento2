<?php

namespace Expressly\Expressly\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('expressly_preferences'))
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Website reference'
            )
            ->addForeignKey(
                'expressly_preferences_website_id_foreign',
                'website_id',
                'store_website',
                'website_id',
                Table::ACTION_CASCADE
            )
            ->addColumn(
                'api_key',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Expressly API Key'
            )
            ->addColumn(
                'merchant_url',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Base url'
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}