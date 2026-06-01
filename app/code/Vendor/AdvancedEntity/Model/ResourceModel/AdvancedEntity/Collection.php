<?php
namespace Vendor\AdvancedEntity\Model\ResourceModel\AdvancedEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * This class represents a collection of AdvancedEntity models.
 * It extends the AbstractCollection class to provide additional
 * functionality specific to the AdvancedEntity resource model.
 *
 * @package Vendor\AdvancedEntity\Model\ResourceModel\AdvancedEntity
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Vendor\AdvancedEntity\Model\AdvancedEntity::class,
            \Vendor\AdvancedEntity\Model\ResourceModel\AdvancedEntity::class
        );
    }
}
