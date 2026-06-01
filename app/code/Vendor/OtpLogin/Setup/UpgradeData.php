<?php
/**
 * Copyright © 2020 Origin. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Origin\OtpLogin\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * UpgradeData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $setup->startSetup();
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'mobile'
            );
            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $customerSetup->addAttribute(Customer::ENTITY, 'mobile', [
            'type' => 'varchar',
            'label' => 'Mobile Number',
            'input' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
            'unique' => false,
        ]);
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'isd_code'
            );
            $customerSetup->addAttribute(Customer::ENTITY, 'isd_code', [
            'type' => 'varchar',
            'label' => 'Isd Code',
            'input' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
            'unique' => false
        ]);
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'zip_code'
            );
            $customerSetup->addAttribute(Customer::ENTITY, 'zip_code', [
            'type' => 'varchar',
            'label' => 'Zip Code',
            'input' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
            'unique' => false,
        ]);
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'mobile_verify'
            );
            $customerSetup->addAttribute(Customer::ENTITY, 'mobile_verify', [
            'type' => 'int',
            'label' => 'Mobile Number Verified?',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'input' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            'required' => false,
            'visible' => true,
            'user_defined' => false,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
            'default' => 0
        ]);
            //add attribute to attribute set
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobile')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create', 'customer_account_edit'],
        ]);
            $attribute->save();
            $attribute3 = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'isd_code')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create', 'customer_account_edit'],
        ]);
            $attribute3->save();
            $attribute4 = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'zip_code')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create', 'customer_account_edit'],
        ]);
            $attribute4->save();
            $attribute2 = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobile_verify')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create','customer_account_edit'],
        ]);
            $attribute2->save();
            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $setup->startSetup();
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'customer_approve'
            );
            $customerSetup->addAttribute(Customer::ENTITY, 'customer_approve', [
            'type' => 'int',
            'label' => 'Approve Customer',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'input' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            'required' => false,
            'visible' => true,
            'user_defined' => false,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
            'default' => 0
        ]);
            //add attribute to attribute set
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_approve')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create', 'customer_account_edit'],
        ]);
            $attribute->save();
        }
        if (version_compare($context->getVersion(), '1.0.11') < 0) {
            
            $setup->startSetup();
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'customer_approve'
            );
            $customerSetup->addAttribute(Customer::ENTITY, 'customer_approve', [
            'type' => 'int',
            'label' => 'Approve Customer',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'input' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            'required' => false,
            'visible' => true,
            'user_defined' => false,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
            'default' => 0,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true
        ]);
            //add attribute to attribute set
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_approve')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create', 'customer_account_edit'],
        ]);
            $attribute->save();

            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(
                Customer::ENTITY,
                'mobile'
            );
            $customerSetup->addAttribute(Customer::ENTITY, 'mobile', [
                'type' => 'varchar',
                'label' => 'Mobile Number',
                'input' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 1000,
                'position' => 1000,
                'system' => 0,
                'unique' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
        ]);
            //add attribute to attribute set
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobile')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_create', 'customer_account_edit'],
        ]);
            $attribute->save();
            $setup->endSetup();
        }
    }
}
