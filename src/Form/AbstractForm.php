<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Extra\HasRatingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractForm extends AbstractType implements FormTypeInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
    }

    public function getRatingChoices(): array
    {
        $ratingChoices = [
            '?' => 0,
        ];
        for ($i = 1; $i <= HasRatingInterface::MAX_RATING; $i++) {
            $ratingChoices[$i] = $i;
        }

        return $ratingChoices;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
