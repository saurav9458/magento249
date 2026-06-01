<?php
namespace Vendor\AdvancedEntity\Api\Data;

/**
 * Interface for Advanced Entity
 */
/**
 * Interface AdvancedEntityInterface
 *
 * Represents the data structure for an advanced entity.
 *
 * @package Vendor\AdvancedEntity\Api\Data
 *
 * Constants:
 *  - ENTITY_ID: The unique identifier for the entity.
 *  - NAME: The name of the entity.
 *  - STATUS: The status of the entity.
 *  - DESCRIPTION: The description of the entity.
 *  - WEBSITE: The website associated with the entity.
 *  - SORT_ORDER: The sort order of the entity.
 *  - IMAGE: The image associated with the entity.
 *
 * Methods:
 *  - getId(): Get the entity ID.
 *  - getName(): Get the entity name.
 *  - getStatus(): Get the entity status.
 *  - getDescription(): Get the entity description.
 *  - getWebsite(): Get the entity website.
 *  - getSortOrder(): Get the entity sort order.
 *  - getImage(): Get the entity image.
 *  - setName(string $name): Set the entity name.
 *  - setStatus(int $status): Set the entity status.
 *  - setDescription(string $description): Set the entity description.
 *  - setWebsite(string $website): Set the entity website.
 *  - setSortOrder(string $sortOrder): Set the entity sort order.
 *  - setImage(string $image): Set the entity image.
 */
interface AdvancedEntityInterface
{
    const ENTITY_ID = 'entity_id';
    const NAME = 'name';
    const STATUS = 'status';
     const CODE = 'code'; 
    const DESCRIPTION = 'description';
    const WEBSITE = 'website';
    const SORT_ORDER = 'sort_order';
    const IMAGE = 'image';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function getId();
    public function getName(): string;
    public function getStatus(): int;
    // public function getCode(): int; --- IGNORE ---
    public function getDescription(): string;
    public function getWebsite(): string;
    public function getSortOrder(): string;
    public function getImage(): string;
    public function getCreatedAt();
    public function getUpdatedAt();


    // public function setId(int $id): self;
    public function setName(string $name): self;
    public function setStatus(int $status): self;
    // public function setCode(): self; --- IGNORE ---
    public function setDescription(string $description): self;
    public function setWebsite(string $website): self;
    public function setSortOrder(string $sortOrder): self;
    public function setImage(string $image): self;
}
