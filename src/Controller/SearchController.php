<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Tag;
use App\Repo\ProductsRepo;
use App\Utils\Transliterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route("/search", name: "search")]
    public function indexAction(Request $request): Response
    {
        $searchParams = $this->getSearchParams($request);
        $searchTerm = $searchParams['term'];

        /** @var ProductsRepo $productsRepo */
        $productsRepo = $this->getRepo(Product::class);

        if ($searchTerm) {
            $products = $productsRepo->searchByTerm($searchTerm);
        } elseif ($searchParams['tag']) {
            $products = $productsRepo->searchByTag($searchParams['tag']);
        } else {
            $products = $productsRepo->findAll();
        }

        return $this->render('search/index.twig', [
            'products' => $products,
            'searchParams' => $searchParams,
        ]);
    }

    private function getSearchTerm(Request $request): string
    {
        $searchTerm = trim($request->get('term', ''));

        if ($searchTerm) {
            $searchTerm = Transliterator::fixKeyboardLayout($searchTerm);
        }

        return $searchTerm;
    }

    private function getSearchParams(Request $request): array
    {
        $params = array_merge(ProductsRepo::getDefaultSearchParams(), [
            'term' => $this->getSearchTerm($request),
        ]);

        if ($request->query->has('tag')) {
            $params['tagId'] = (int) $request->query->get('tag');
            $params['tag'] = $this->findOr404(Tag::class, $params['tagId']);
        }

        if ($request->query->has('order_dir')) {
            $params['orderDir'] = $request->query->get('order_dir');
        }

        if ($request->query->has('order_by')) {
            $params['orderBy'] = $request->query->get('order_by');
        }

        return $params;
    }
}
