<?php
namespace Vendor\AdvancedEntity\Model;

use Vendor\AdvancedEntity\Api\AdvancedEntityRepositoryInterface;
use Vendor\AdvancedEntity\Api\Data\AdvancedEntityInterface;
use Vendor\AdvancedEntity\Model\ResourceModel\AdvancedEntity as Resource;
use Vendor\AdvancedEntity\Model\AdvancedEntityFactory;

/**
 * Repository implementation
 */
class AdvancedEntityRepository implements AdvancedEntityRepositoryInterface
{
    private Resource $resource;
    private AdvancedEntityFactory $factory;

    public function __construct(Resource $resource, AdvancedEntityFactory $factory)
    {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    public function save(\Vendor\AdvancedEntity\Api\Data\AdvancedEntityInterface $entity)
    {
        try {
            $this->resource->save($entity);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Unable to save entity'),
                $e
            );
        }
        return $entity;
    }

    public function getById(int $id)
    {
        $entity = $this->factory->create();
        $this->resource->load($entity, $id);
        return $entity;
    }

    public function deleteById(int $id): bool
    {
        $entity = $this->getById($id);
        $this->resource->delete($entity);
        return true;
    }
}
