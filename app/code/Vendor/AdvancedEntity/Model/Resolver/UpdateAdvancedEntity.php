<?php
namespace Vendor\AdvancedEntity\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\AdvancedEntity\Api\AdvancedEntityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class UpdateAdvancedEntity implements ResolverInterface
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
        // if (empty($args['entity_id'])) {
        //     throw new \InvalidArgumentException(__('Entity ID is required.'));
        // }

        try {
            $this->validateRequiredParams($args, ['entity_id', 'name', 'status','description','website','sort_order','image']);
            $entity = $this->advancedEntityRepository->getById((int)$args['entity_id']);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\GraphQlNoSuchEntityException(
                __('Advanced entity does not exist.')
            );
        }

        if (isset($args['name'])) {
            $entity->setName($args['name']);
        }

        if (isset($args['status'])) {
            $entity->setStatus($args['status']);
        }
        if (isset($args['description'])) {
            $entity->setDescription($args['description']);
        }
        if (isset($args['website'])) {
            $entity->setWebsite($args['website']);
        }
        if (isset($args['sort_order'])) {
            $entity->setSortOrder($args['sort_order']);
        }
        if (isset($args['image'])) {
            $entity->setImage($args['image']);
        } else {
            $entity->setImage('');
        }                                                 

        $this->advancedEntityRepository->save($entity);

        return $entity;
    }

    /**
         * Validate required parameters.
         *
         * @param array|null $args
         * @param array $requiredParams
         * @throws GraphQlInputException
         */
    private function validateRequiredParams(?array $args, array $requiredParams)
        {
        foreach ($requiredParams as $param) {
            if (!isset($args[$param])) {
            throw new GraphQlInputException(__('Missing required parameter: %1.', $param));
            }
        }
        }
}
