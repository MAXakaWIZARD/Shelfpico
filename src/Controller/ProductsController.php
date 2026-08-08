<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductForm;
use App\Repo\ProductsRepo;
use App\Service\DbSync;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProductsController extends AbstractController
{
    /**
     * @var Product
     */
    protected $entity;

    /**
     * @var DbSync
     */
    private $dbSync;

    public function __construct(
        DbSync $dbSync
    ) {
        $this->dbSync = $dbSync;
    }

    #[Route("/products", name: "products")]
    public function indexAction(): Response
    {
        $criteria = [];

        /** @var ProductsRepo $repo */
        $repo = $this->getRepo(Product::class);

        return $this->render('products/index.twig', [
            'title' => 'Products',
            'products' => $repo->findBy($criteria, ['title' => 'ASC']),
        ]);
    }

    #[Route("/products/{id}", name: "products.edit", requirements: ["id" => "[0-9]+"])]
    #[Route("/products/add", name: "products.add")]
    public function editAction(?int $id): Response
    {
        if ($this->entity) {
            $entity = $this->entity;
        } elseif ($id) {
            $entity = $this->findOr404(Product::class, $id);
        } else {
            $entity = new Product();
        }

        $form = $this->createForm(ProductForm::class, $entity);

        return $this->render('products/edit.twig', [
            'product' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/products/save", name: "products.save", methods: ["POST"])]
    public function saveAction(Request $request): Response
    {
        $id = intval($request->request->get('id'));
        $isEdit = $id > 0;
        $entity = $isEdit ? $this->findOr404(Product::class, $id) : new Product();

        $form = $this->createForm(ProductForm::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Product $entity */
            $entity = $form->getData();

            if (!$entity->getId()) {
                $this->em->persist($entity);
            }

            $this->em->flush();

            $file = $form['photo']->getData();
            if ($file instanceof UploadedFile) {
                $destinationPath = $entity->getPhotoPath(true);
                $file->move(dirname($destinationPath), basename($destinationPath));
            }

            $this->addFlash('success', $isEdit ? 'Saved' : 'Created');

            return $this->redirectToRoute('products.edit', ['id' => $entity->getId()]);
        } else {
            $this->addFlash('error', $this->getFormErrorsAsString($form));
            $this->entity = $entity;

            return $this->editAction($entity->getId());
        }
    }

    #[Route("/products/update-stocks", name: "products.update-stocks")]
    public function updateStocksAction(): Response
    {
        /** @var ProductsRepo $repo */
        $repo = $this->getRepo(Product::class);
        $repo->updateStocks();

        $this->dbSync->exportStocks();

        return $this->redirectToRoute('home');
    }
}
