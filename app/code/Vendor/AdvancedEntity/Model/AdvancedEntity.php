<?php
namespace Vendor\AdvancedEntity\Model;

use Vendor\AdvancedEntity\Api\Data\AdvancedEntityInterface;
use Magento\Framework\Model\AbstractModel;


/**
 * Class AdvancedEntity
 *
 * This class represents an advanced entity model that implements the EntityInterface.
 * It extends the AbstractModel, providing additional functionality specific to advanced entities.
 *
 * @package Vendor\AdvancedEntity\Model
 * @extends AbstractModel
 * @implements EntityInterface
 */
class AdvancedEntity extends AbstractModel implements AdvancedEntityInterface
{
    protected function _construct()
    {
        $this->_init(\Vendor\AdvancedEntity\Model\ResourceModel\AdvancedEntity::class);
    }


    public function getId()
    {
        return $this->getData('entity_id');
    }

    public function setId($id)
    {
        return $this->setData('entity_id', $id);
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    public function getCode(): int
    {
        return (int)$this->getData(self::CODE);
    }

    public function setCode(): self
    {
        return $this->setData(self::CODE, $this->generateUniqueCode());
    }

    public function getDescription(): string
    {
        return (string)$this->getData(self::DESCRIPTION);
    }

    public function setDescription(string $description): self
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getSortOrder(): string
    {
        return (string)$this->getData(self::SORT_ORDER);
    }

    public function setSortOrder(string $sortOrder): self
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function getImage(): string
    {
        return (string)$this->getData(self::IMAGE);
    }

    public function setImage(string $image): self
    {    
        return $this->setData(self::IMAGE, $image);
    }

    public function getCreatedAt()
    {
        return ($this->getData(self::CREATED_AT));
    }

    // public function setCreatedAt(string $createdAt): self
    // {    
    //     return $this->setData(self::CREATED_AT, $createdAt);
    // }

    public function getUpdatedAt()
    {
        return ($this->getData(self::UPDATED_AT));
    }

    // public function setUpdatedAt(string $updatedAt): self
    // {    
    //     return $this->setData(self::UPDATED_AT, $updatedAt);
    // }

    public function getWebsite(): string
    {
        return (string)$this->getData(self::WEBSITE);
    }

    public function setWebsite(string $website): self
    {    
        return $this->setData(self::WEBSITE, $website);
    }

     
    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function setStatus(int $status): self
    {
        return $this->setData(self::STATUS, $status);
    }

    private function generateUniqueCode(): int
    {
        // Generate a unique code using a combination of current timestamp and a random number
        return (int)(time() . rand(1000, 9999));
    }

}
