<?php
namespace Vendor\AdvancedEntity\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


/**
 * Class AdvancedEntity
 *
 * This class represents the resource model for the AdvancedEntity entity.
 * It extends the AbstractDb class to provide database interaction capabilities.
 *
 * @package Vendor\AdvancedEntity\Model\ResourceModel
 */
class AdvancedEntity extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('advanced_entity', 'entity_id');
    }
}
