<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Origin\OtpLogin\Model\OriginOtpConstants;

/**
 * Class InstallSchema
 * @package Origin\OtpLogin\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable(OriginOtpConstants::ORIGIN_OTP_TABLE);
        /**
         * Create table 'origin_customer_mobile_otp'
         */
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'mobile_number',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Mobile Number'
                )
                ->addColumn(
                    'otp',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'OTP'
                )
                ->addColumn(
                    'mobile_verified',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true],
                    'Mobile Verified'
                )
                ->addColumn(
                    'expiry',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false],
                    'Expiry'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                );
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
