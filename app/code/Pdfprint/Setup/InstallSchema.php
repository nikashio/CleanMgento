<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Setup;

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
        /**
         * Create table 'snm_pdfprint_template'
         */
        $table = $installer->getConnection()
                           ->newTable(
                               $installer->getTable('snm_pdfprint_template')
                           )
                           ->addColumn(
                               'template_id',
                               Table::TYPE_SMALLINT,
                               null,
                               ['identity' => true, 'nullable' => false, 'primary' => true],
                               'Template ID'
                           )
                           ->addColumn(
                               'title',
                               Table::TYPE_TEXT,
                               255,
                               ['nullable' => true],
                               'Page Title'
                           )
                           ->addColumn(
                               'identifier',
                               Table::TYPE_TEXT,
                               100,
                               ['nullable' => true, 'default' => null],
                               'Template String Identifier'
                           )
                           ->addColumn(
                               'content',
                               Table::TYPE_TEXT,
                               '2M',
                               [],
                               'Template Content'
                           )
                           ->addColumn(
                               'content2',
                               Table::TYPE_TEXT,
                               '2M',
                               [],
                               'Template Content2'
                           )
                           ->addColumn(
                               'content3',
                               Table::TYPE_TEXT,
                               '2M',
                               [],
                               'Template Content3'
                           )
                           ->addColumn(
                               'creation_time',
                               Table::TYPE_TIMESTAMP,
                               null,
                               [],
                               'Creation Time'
                           )
                           ->addColumn(
                               'update_time',
                               Table::TYPE_TIMESTAMP,
                               null,
                               [],
                               'Modification Time'
                           )
                           ->addColumn(
                               'is_active',
                               Table::TYPE_SMALLINT,
                               null,
                               ['nullable' => false, 'default' => '1'],
                               'Is Page Active'
                           )
                           ->addColumn(
                               'sort_order',
                               Table::TYPE_SMALLINT,
                               null,
                               ['nullable' => false, 'default' => '0'],
                               'Page Sort Order'
                           )
                           ->addColumn(
                               'layout_update_xml',
                               Table::TYPE_TEXT,
                               '64k',
                               ['nullable' => true],
                               'Page Layout Update Content'
                           )
                           ->addIndex(
                               $installer->getIdxName('snm_pdfprint_template', ['identifier']),
                               ['identifier']
                           )
                           ->setComment(
                               'SNM-PORTAL PDFPRINT Template Table'
                           );
        $installer->getConnection()
                  ->createTable($table);

        /**
         * Create table 'snm_pdfprint_template_store'
         */
        $table = $installer->getConnection()
                           ->newTable(
                               $installer->getTable('snm_pdfprint_template_store')
                           )
                           ->addColumn(
                               'template_id',
                               Table::TYPE_SMALLINT,
                               null,
                               ['nullable' => false, 'primary' => true],
                               'Template ID'
                           )
                           ->addColumn(
                               'store_id',
                               Table::TYPE_SMALLINT,
                               null,
                               ['unsigned' => true, 'nullable' => false, 'primary' => true],
                               'Store ID'
                           )
                           ->addIndex(
                               $installer->getIdxName('snm_pdfprint_template_store', ['store_id']),
                               ['store_id']
                           )
                           ->addForeignKey(
                               $installer->getFkName(
                                   'snm_pdfprint_template_store',
                                   'template_id',
                                   'snm_pdfprint_template',
                                   'template_id'
                               ),
                               'template_id',
                               $installer->getTable('snm_pdfprint_template'),
                               'template_id',
                               Table::ACTION_CASCADE
                           )
                           ->addForeignKey(
                               $installer->getFkName('snm_pdfprint_template_store', 'store_id', 'store', 'store_id'),
                               'store_id',
                               $installer->getTable('store'),
                               'store_id',
                               Table::ACTION_CASCADE
                           )
                           ->setComment(
                               'SNM-PORTAL PDFPRINT Template Store Linkage Table'
                           );
        $installer->getConnection()
                  ->createTable($table);


        $installer->endSetup();
    }
}
