<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CrudController extends AbstractController
{
    #[Route("/restocks/delete", name: "restocks.delete", defaults: ["entityName" => "Restock"])]
    #[Route("/orders/delete", name: "orders.delete", defaults: ["entityName" => "Order"])]
    #[Route("/products/delete", name: "products.delete", defaults: ["entityName" => "Product"])]
    #[Route("/customers/delete", name: "customers.delete", defaults: ["entityName" => "Customer"])]
    #[Route("/tags/delete", name: "tags.delete", defaults: ["entityName" => "Tag"])]
    #[Route("/settings/delete", name: "settings.delete", defaults: ["entityName" => "Setting"])]
    public function deleteAction(Request $request, string $entityName): Response
    {
        $id = intval($request->query->get('id'));
        if (!$id || !$entityName) {
            $this->abort(Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->em->find('App\\Entity\\' . $entityName, $id);
        if (!is_object($entity)) {
            $this->abort(Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($entity);
        $this->em->flush();

        return new Response();
    }
}
