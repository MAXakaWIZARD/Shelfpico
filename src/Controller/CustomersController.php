<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerForm;
use App\Repo\CustomersRepo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomersController extends AbstractController
{
    /**
     * @var Customer
     */
    protected $entity;

    #[Route("/customers", name: "customers")]
    public function indexAction(): Response
    {
        /** @var CustomersRepo $repo */
        $repo = $this->getRepo(Customer::class);

        return $this->render('customers/index.twig', [
            'title' => 'Customers',
            'customers' => $repo->findBy([], ['name' => 'ASC']),
            'profitStats' => $repo->getProfitStats(),
        ]);
    }

    #[Route("/customers/{id}", name: "customers.edit", requirements: ["id" => "[0-9]+"])]
    #[Route("/customers/add", name: "customers.add")]
    public function editAction(?int $id): Response
    {
        if ($this->entity) {
            $entity = $this->entity;
        } elseif ($id) {
            $entity = $this->findOr404(Customer::class, $id);
        } else {
            $entity = new Customer();
        }

        $form = $this->createForm(CustomerForm::class, $entity);

        return $this->render('customers/edit.twig', [
            'customer' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/customers/save", name: "customers.save", methods: ["POST"])]
    public function saveAction(Request $request): Response
    {
        $id = intval($request->request->get('id'));
        $isEdit = $id > 0;
        $entity = $isEdit ? $this->findOr404(Customer::class, $id) : new Customer();

        $form = $this->createForm(CustomerForm::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            if (!$entity->getId()) {
                $this->em->persist($entity);
            }

            $this->em->flush();

            $this->addFlash('success', $isEdit ? 'Saved' : 'Created');

            return $this->redirectToRoute('customers.edit', ['id' => $entity->getId()]);
        } else {
            $this->addFlash('error', $this->getFormErrorsAsString($form));
            $this->entity = $entity;

            return $this->editAction($entity->getId());
        }
    }
}
