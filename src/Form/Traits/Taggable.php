<?php

declare(strict_types=1);

namespace App\Form\Traits;

use App\Entity\Tag;
use App\Form\AbstractForm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

trait Taggable
{
    protected function processTags(array $data): ArrayCollection
    {
        /** @var AbstractForm $this */

        if (!array_key_exists('tag_ids', $data)) {
            $data['tag_ids'] = [];
        }

        $tagsRepo = $this->em->getRepository(Tag::class);
        $tags = new ArrayCollection();
        foreach ($data['tag_ids'] as $tagId) {
            $tags->add($tagsRepo->find($tagId));
        }

        return $tags;
    }

    protected function addTagsFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('tags', HiddenType::class, [
                'required' => false,
                'data' => '',
            ])
        ;
    }
}
