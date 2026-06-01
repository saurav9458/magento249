<?php
namespace Vendor\AdvancedEntity\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\AdvancedEntity\Api\AdvancedEntityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Resolver class for fetching advanced entity data.
 *
 * Implements the ResolverInterface to provide logic for retrieving
 * advanced entity information, typically used in GraphQL or API contexts.
 *
 * @package Vendor\AdvancedEntity\Model\Resolver
 */
class GetAdvancedEntity implements ResolverInterface
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
            return $this->advancedEntityRepository->getById((int)$args['entity_id']);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\GraphQlNoSuchEntityException(
                __('Advanced entity does not exist.')
            );
        }
    }
}
