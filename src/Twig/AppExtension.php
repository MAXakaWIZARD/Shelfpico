<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Product;
use App\Entity\Tag;
use App\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    protected $tags;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        Environment $twig,
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag
    ) {
        $this->em = $em;
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'get_tags',
                [$this, 'getTags'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'get_parameter',
                [$this, 'getParameter'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'get_product_photo_url',
                [$this, 'getProductPhotoUrl'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'process_output',
                function (Process $process) {
                    return Utils::getHtmlFriendlyProcessOutput($process);
                },
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('dechex', 'dechex'),
            new TwigFilter('assetize', [$this, 'assetize']),
            new TwigFilter('format_price', [$this, 'formatPrice']),
        ];
    }

    public function assetize($url): string
    {
        if ($url) {
            return $url . '?v=' . time();
        }

        return '';
    }

    public function formatPrice($content): string
    {
        return number_format((float) $content, 0, '.', ' ');
    }

    /**
     * @return mixed
     */
    public function getParameter(string $paramName)
    {
        return $this->parameterBag->get($paramName);
    }


    public function getTags(): array
    {
        if (!is_null($this->tags)) {
            return $this->tags;
        }

        $this->tags = $this->em->getRepository(Tag::class)->findBy([], ['title' => 'ASC']);

        return $this->tags;
    }

    public function getProductPhotoUrl(Product $product): ?string
    {
        if ($product->hasPhoto()) {
            return PRODUCT_IMAGES_SUBDIR . $product->getPhotoPath();
        }

        return null;
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}
