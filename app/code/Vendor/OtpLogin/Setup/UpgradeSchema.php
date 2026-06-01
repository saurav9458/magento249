<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Origin\OtpLogin\Model\OriginOtpConstants;

/**
 * Class UpgradeSchema
 * @package Origin\OtpLogin\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Function to upgrade DB schema
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $tableName = $setup->getTable(OriginOtpConstants::ORIGIN_OTP_TABLE);
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $setup->getConnection()->changeColumn(
                    $tableName,
                    'mobile_number',
                    'mobile_number',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 20,
                        'comment' => "otp code",
                        'nullable' => false
                    ]
                );
                $setup->getConnection()->changeColumn(
                    $tableName,
                    'otp',
                    'otp',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 10,
                        'comment' => "otp code",
                        'nullable' => false
                    ]
                );
                $setup->getConnection()->changeColumn(
                    $tableName,
                    'mobile_verified',
                    'mobile_verified',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'length' => null,
                        'comment' => "otp code",
                        'nullable' => false
                    ]
                );
                $setup->getConnection()->changeColumn(
                    $tableName,
                    'expiry',
                    'expiry',
                    [
                        'type' => Table::TYPE_DATETIME,
                        'length' => null,
                        'comment' => "otp expiry time",
                        'nullable' => false
                    ]
                );
                $setup->getConnection()->changeColumn(
                    $tableName,
                    'created_at',
                    'created_at',
                    [
                        'type' => Table::TYPE_DATETIME,
                        'length' => null,
                        'comment' => "otp send time",
                        'nullable' => false
                    ]
                );
                $setup->getConnection()->changeColumn(
                    $tableName,
                    'updated_at',
                    'updated_at',
                    [
                        'type' => Table::TYPE_DATETIME,
                        'length' => null,
                        'comment' => "otp update time",
                        'nullable' => true,
                        'default' => null
                    ]
                );
            }
        }
        $setup->endSetup();
    }
}
