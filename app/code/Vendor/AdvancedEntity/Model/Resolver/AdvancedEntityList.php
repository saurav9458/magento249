<?php
namespace Vendor\AdvancedEntity\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\AdvancedEntity\Model\ResourceModel\AdvancedEntity\CollectionFactory;

/**
 * Resolver for retrieving a list of advanced entities.
 *
 * Implements the ResolverInterface to provide data fetching logic
 * for advanced entities in GraphQL queries.
 *
 * @package Vendor\AdvancedEntity\Model\Resolver
 */
class AdvancedEntityList implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $pageSize = $args['pageSize'] ?? 10;
        $currentPage = $args['currentPage'] ?? 1;

        $collection = $this->collectionFactory->create();
        $collection->setPageSize((int)$pageSize);
        $collection->setCurPage((int)$currentPage);

        $items = [];
        foreach ($collection as $entity) {
            $items[] = [
                'entity_id' => (int)$entity->getId(),
                'name'      => $entity->getName(),
                'status'    => (int)$entity->getStatus(),
                'code'    => (int)$entity->getCode(),
                'description'    => $entity->getDescription(),
                'website'    => $entity->getWebsite(),
                'sort_order'    => $entity->getSortOrder(),
                'image'    => $entity->getImage(),
                'created_at' => $entity->getCreatedAt(),
                'updated_at' => $entity->getUpdatedAt()
            ];
        }

        return [
            'items' => $items,
            'total_count' => $collection->getSize()
        ];
    }
}
