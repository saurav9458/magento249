<?php
namespace Vendor\AdvancedEntity\Api;

use Vendor\AdvancedEntity\Api\Data\AdvancedEntityInterface;

/**
 * Interface AdvancedEntityRepositoryInterface
 *
 * Defines the contract for repository operations on advanced_entity entities.
 *
 * @package Vendor\AdvancedEntity\Api
 */

interface AdvancedEntityRepositoryInterface
{
    /**
     * Save the given advanced entity.
     *
     * @param AdvancedEntityInterface $entity The entity to be saved.
     * @return AdvancedEntityInterface The saved entity.
     * @throws \Magento\Framework\Exception\LocalizedException If the entity could not be saved.
     */
    public function save(AdvancedEntityInterface $entity);
    /**
     * Retrieve an entity by its unique identifier.
     *
     * @param int $id The unique identifier of the entity.
     * @return \Vendor\AdvancedEntity\Api\Data\AdvancedEntityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the entity does not exist.
     */
    public function getById(int $id);

    /**
     * Delete the entity by its unique identifier.
     *
     * @param int $id The unique identifier of the entity to delete.
     * @return bool True if the entity was successfully deleted, false otherwise.
     */
    public function deleteById(int $id): bool;
}
