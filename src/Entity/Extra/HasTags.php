<?php

declare(strict_types=1);

namespace App\Entity\Extra;

use App\Entity\Tag;

trait HasTags
{
    /**
     * @return Tag[]
     */
    public function getTags(): iterable
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags(iterable $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    public function hasTag(string $tagTitle): bool
    {
        $tags = $this->getTags();
        foreach ($tags as $tag) {
            if ($tag->getTitle() === $tagTitle) {
                return true;
            }
        }

        return false;
    }

    public function hasTagPrefix(string $tagPrefix): bool
    {
        $tags = $this->getTags();
        foreach ($tags as $tag) {
            if (strpos($tag->getTitle(), $tagPrefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
