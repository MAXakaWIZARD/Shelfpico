<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DbVersion;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Restock;
use App\Repo\DbVersionsRepo;
use App\Repo\OrdersRepo;
use App\Repo\ProductsRepo;
use App\Repo\RestocksRepo;
use App\Service\DbSync;
use App\Utils\EnvChecker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    /**
     * @var DbSync
     */
    private $dbSync;

    /**
     * @var EnvChecker
     */
    private $envChecker;

    public function __construct(
        DbSync $dbSync,
        EnvChecker $envChecker
    ) {
        $this->dbSync = $dbSync;
        $this->envChecker = $envChecker;
    }

    #[Route("/", name: "home")]
    public function indexAction(): Response
    {
        /** @var DbVersionsRepo $versionsRepo */
        $versionsRepo = $this->getRepo(DbVersion::class);
        $currentDbVersion = $versionsRepo->getCurrentVersion();

        if (
            $this->getParameter('kernel.environment') !== 'test'
            && $currentDbVersion < $this->dbSync->getLatestDbVersion()
        ) {
            return $this->redirectToRoute('db.import');
        }

        /** @var OrdersRepo $ordersRepo */
        $ordersRepo = $this->getRepo(Order::class);

        /** @var ProductsRepo $productsRepo */
        $productsRepo = $this->getRepo(Product::class);

        /** @var RestocksRepo $restocksRepo */
        $restocksRepo = $this->getRepo(Restock::class);

        $products = $productsRepo->getProductsForDashboard();

        return $this->render('index/index.twig', [
            'currentDbVersion' => $currentDbVersion,
            'totalProfit' => $ordersRepo->getTotalProfit(),
            'inStockSalePrice' => $productsRepo->getSalePriceUah($products),
            'inStockBuyPrice' => $productsRepo->getBuyPriceUah($products),
            'totalSpentAmount' => $restocksRepo->getTotalSpentAmount(),
            'totalReceivedAmount' => $ordersRepo->getTotalSpentAmount(),
            'products' => $products,
        ]);
    }

    #[Route("/dashboard/tags-stats", name: "dashboard.tags-stats")]
    public function tagsStatsAction(): Response
    {
        /** @var ProductsRepo $repo */
        $repo = $this->getRepo(Product::class);

        return $this->render('index/dashboard/tags-stats.twig', [
            'tagsStats' => $repo->getTagsStats(),
        ]);
    }

    #[Route("/env-check", name: "env.check")]
    public function envCheckAction(): Response
    {
        return $this->render('index/env-check.twig', [
            'env' => $this->envChecker->getEnvVars(),
        ]);
    }
}
