<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Restock;
use App\Form\RestockForm;
use App\Repo\ProductsRepo;
use App\Repo\RestocksRepo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RestocksController extends AbstractController
{
    /**
     * @var Restock
     */
    protected $entity;

    #[Route("/restocks", name: "restocks")]
    public function indexAction(): Response
    {
        $criteria = [];

        /** @var RestocksRepo $repo */
        $repo = $this->getRepo(Restock::class);

        return $this->render('restocks/index.twig', [
            'title' => 'Restocks',
            'restocks' => $repo->findBy($criteria, ['createdAt' => 'DESC']),
        ]);
    }

    #[Route("/restocks/product/{id}", name: "restocks.product", requirements: ["id" => "[0-9]+"])]
    public function productRestocksAction(int $id): Response
    {
        $product = $this->findOr404(Product::class, $id);

        return $this->render('restocks/index.twig', [
            'title' => 'Restocks of ' . $product->getTitle(),
            'restocks' => $this->getRepo(Restock::class)->findBy(['product' => $product], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route("/restocks/{id}", name: "restocks.edit", requirements: ["id" => "[0-9]+"])]
    #[Route("/restocks/add", name: "restocks.add")]
    public function editAction(?int $id): Response
    {
        if ($this->entity) {
            $entity = $this->entity;
        } elseif ($id) {
            $entity = $this->findOr404(Restock::class, $id);
        } else {
            $entity = new Restock();
            $entity->setQuantity(1);
        }

        $form = $this->createForm(RestockForm::class, $entity);

        return $this->render('restocks/edit.twig', [
            'restock' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/restocks/save", name: "restocks.save", methods: ["POST"])]
    public function saveAction(Request $request): Response
    {
        $id = intval($request->request->get('id'));
        $isEdit = $id > 0;
        $entity = $isEdit ? $this->findOr404(Restock::class, $id) : new Restock();

        $form = $this->createForm(RestockForm::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            if (!$entity->getId()) {
                $this->em->persist($entity);
            }

            $this->em->flush();

            $this->addFlash('success', $isEdit ? 'Saved' : 'Created');

            /** @var ProductsRepo $productsRepo */
            $productsRepo = $this->getRepo(Product::class);
            $productsRepo->updateStocks();

            return $this->redirectToRoute('restocks.edit', ['id' => $entity->getId()]);
        } else {
            $this->addFlash('error', $this->getFormErrorsAsString($form));
            $this->entity = $entity;

            return $this->editAction($entity->getId());
        }
    }
}
