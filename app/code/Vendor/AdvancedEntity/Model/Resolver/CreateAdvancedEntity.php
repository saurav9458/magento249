<?php
namespace Vendor\AdvancedEntity\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\AdvancedEntity\Api\AdvancedEntityRepositoryInterface;
use Vendor\AdvancedEntity\Model\AdvancedEntityFactory as AdvancedEntityInterfaceFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Psr\Log\LoggerInterface;

/**
 * Class CreateAdvancedEntity
 *
 * Resolver for creating advanced entities.
 * Implements ResolverInterface to handle GraphQL mutations for entity creation.
 *
 * @package Vendor\AdvancedEntity\Model\Resolver
 */
class CreateAdvancedEntity implements ResolverInterface
{
    private $advancedEntityRepository;
    private $advancedEntityFactory;
    private $logger;

    public function __construct(
        AdvancedEntityRepositoryInterface $advancedEntityRepository,
        AdvancedEntityInterfaceFactory $advancedEntityFactory,
        LoggerInterface $logger
    ) {
        $this->advancedEntityRepository = $advancedEntityRepository;
        $this->advancedEntityFactory = $advancedEntityFactory;
        $this->logger = $logger;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateRequiredParams($args, ['name', 'status','description','website','sort_order','image']);

        try {
            $entity = $this->advancedEntityFactory->create();
            $entity->setName($args['name']);
            $entity->setStatus((int)$args['status']);
            // Generate a unique code if not set
            $entity->setCode(uniqid('adv_', true));
            $entity->setDescription($args['description'] ?? '');
            $entity->setWebsite($args['website'] ?? '');
            $entity->setSortOrder($args['sort_order'] ?? 0);
            $entity->setImage($args['image'] ?? '');
            $entity = $this->advancedEntityRepository->save($entity);
            if (!$entity->getId()) {
                throw new \RuntimeException('Advanced entity was NOT saved');
            }

            // FIELD NAMES MUST MATCH schema.graphqls
            return [
                'entity_id' => (int)$entity->getId(),
                'name'      => $entity->getName(),
                'status'    => (int)$entity->getStatus(),
                'code'      => $entity->getCode(),
                'description' => $entity->getDescription(),
                'website'    => $entity->getWebsite(),
                'sort_order' => $entity->getSortOrder(),
                'image'      => $entity->getImage(),
                'created_at' => $entity->getCreatedAt(),
                'updated_at' => $entity->getUpdatedAt()
            ];
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __('Unable to create advanced entity: %1', $e->getMessage())
            );
        }
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
