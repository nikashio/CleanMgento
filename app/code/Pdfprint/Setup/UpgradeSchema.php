<?php

namespace Snmportal\Pdfprint\Setup;

use Exception;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @param Reader     $moduleReader
     * @param File       $file
     * @param Filesystem $filesystem
     *
     * @internal param \Magento\Framework\Module\Dir\Reader $
     */
    private $filesystem;

    /**
     * UpgradeSchema constructor.
     *
     * @param Reader     $moduleReader
     * @param File       $file
     * @param Filesystem $filesystem
     */
    public function __construct(
        Reader $moduleReader,
        File $file,
        Filesystem $filesystem
    ) {
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            //$connection = $setup->getConnection();
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable('snm_pdfprint_template'),
                      'type',
                      [
                          'type'     => Table::TYPE_SMALLINT,
                          'unsigned' => true,
                          'nullable' => false,
                          'default'  => '0',
                          'comment'  => 'Template Type'
                      ]
                  );
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            //$connection = $setup->getConnection();
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable('snm_pdfprint_template'),
                      'is_default',
                      [
                          'type'     => Table::TYPE_SMALLINT,
                          'unsigned' => true,
                          'nullable' => false,
                          'default'  => '0',
                          'comment'  => 'Template Default'
                      ]
                  );
        }
        if (version_compare($context->getVersion(), '2.7.1', '<')) {
            //$connection = $setup->getConnection();
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable('snm_pdfprint_template'),
                      'is_massaction',
                      [
                          'type'     => Table::TYPE_SMALLINT,
                          'unsigned' => true,
                          'nullable' => false,
                          'default'  => '0',
                          'comment'  => 'MassAction Template'
                      ]
                  );
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection = $setup->getConnection();
            $connection->modifyColumn(
                $setup->getTable('snm_pdfprint_template'),
                'content',
                [
                    'type'   => Table::TYPE_BLOB,
                    'LENGTH' => '4G'
                ]
            );
            $connection->modifyColumn(
                $setup->getTable('snm_pdfprint_template'),
                'content2',
                [
                    'type'   => Table::TYPE_TEXT,
                    'LENGTH' => '4G'
                ]
            );
            $connection->modifyColumn(
                $setup->getTable('snm_pdfprint_template'),
                'content3',
                [
                    'type'   => Table::TYPE_TEXT,
                    'LENGTH' => '4G'
                ]
            );
        }
        // Check for old Directories
        try {
            $etcDir = $this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Snmportal_Pdfprint');
            $moduleDir = $this->file->getParentDirectory($etcDir);
            $rmDir = $moduleDir . '/Test/M1';
            if ($this->file->isDirectory($rmDir)) {
                $this->file->deleteDirectory($rmDir);
            }
            $rmDir = $moduleDir . '/Service/Pdf';
            if ($this->file->isDirectory($rmDir)) {
                $this->file->deleteDirectory($rmDir);
            }
        } catch (Exception $e) {

        }
        $setup->endSetup();
    }
}
