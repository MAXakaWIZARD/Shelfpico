<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IntEntityTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entityType;

    public function __construct(ObjectManager $em, string $entityType)
    {
        $this->em = $em;
        $this->entityType = $entityType;
    }

    /**
     * Transforms entity to int (id).
     *
     * @param object|null $entity
     * @return string
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return '';
        }

        return $entity->getId();
    }

    /**
     * Transforms int (id) to entity.
     *
     * @param  string $entityId
     *
     * @return object|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($entityId)
    {
        // No id? It's optional, so that's ok
        if (!$entityId) {
            return null;
        }

        $issue = $this->em->find($this->entityType, $entityId);

        if (null === $issue) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An entity "%s" with id "%s" does not exist!',
                $this->entityType,
                $entityId
            ));
        }

        return $issue;
    }
}
