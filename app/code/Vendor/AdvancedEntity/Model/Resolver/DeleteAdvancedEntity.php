<?php
namespace Vendor\AdvancedEntity\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\AdvancedEntity\Api\AdvancedEntityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Resolver for deleting an advanced entity.
 *
 * This class implements the ResolverInterface and provides the logic
 * required to delete an advanced entity within the system.
 *
 * @package Vendor_AdvancedEntity
 */
class DeleteAdvancedEntity implements ResolverInterface
{
    /**
     * @var AdvancedEntityRepositoryInterface
     */
    private $advancedEntityRepository;

    public function __construct(
        AdvancedEntityRepositoryInterface $advancedEntityRepository
    ) {
        $this->advancedEntityRepository = $advancedEntityRepository;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['entity_id'])) {
            throw new \InvalidArgumentException(__('Entity ID is required.'));
        }

        try {
            $this->advancedEntityRepository->deleteById((int)$args['entity_id']);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\GraphQlNoSuchEntityException(
                __('Advanced entity does not exist.')
            );
        }

        return [
            'success' => true,
            'message' => __('Advanced entity deleted successfully.')
        ];
    }
}
