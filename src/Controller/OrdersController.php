<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\OrderForm;
use App\Repo\ProductsRepo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OrdersController extends AbstractController
{
    /**
     * @var Order
     */
    protected $entity;

    #[Route("/orders", name: "orders")]
    public function indexAction(): Response
    {
        return $this->render('orders/index.twig', [
            'title' => 'Orders',
            'orders' => $this->getRepo(Order::class)->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route("/orders/shipment", name: "orders.shipment")]
    public function shipmentAction(): Response
    {
        $orders = $this->getRepo(Order::class)->findBy(['shipped' => 0], ['createdAt' => 'DESC']);

        return $this->render('orders/shipment.twig', [
            'title' => 'Orders to ship',
            'data' => $this->aggregateOrders($orders),
        ]);
    }

    #[Route("/orders/aggregated", name: "orders.aggregated")]
    public function aggregatedAction(): Response
    {
        $orders = $this->getRepo(Order::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('orders/aggregated.twig', [
            'title' => 'Orders aggregated',
            'data' => $this->aggregateOrders($orders),
        ]);
    }

    #[Route("/orders/receipt/{key}", name: "orders.receipt")]
    public function receiptAction(string $key, Request $request): Response
    {
        $orders = $this->getRepo(Order::class)->findByKey($key);

        return $this->render('orders/receipt.twig', [
            'title' => 'Receipt',
            'data' => $this->aggregateOrders($orders)[$key] ?? [],
            'lang' => $request->query->get('lang') ?? 'EN',
        ]);
    }

    /**
     * @param Order[] $orders
     */
    protected function aggregateOrders(array $orders): array
    {
        $data = [];

        foreach ($orders as $order) {
            $key = $order->getCustomer()->getId() . '_' . $order->getCreatedAt()->format('Y-m-d');

            if (isset($data[$key])) {
                $record = $data[$key];
            } else {
                $record = [
                    'customer' => $order->getCustomer(),
                    'orders' => [],
                    'paid' => true,
                    'shipped' => true,
                    'totalAmount' => 0,
                    'totalProfit' => 0,
                ];
            }

            $record['orders'][] = $order;
            $record['paid'] = $record['paid'] && $order->isPaid();
            $record['shipped'] = $record['shipped'] && $order->isShipped();
            $record['totalAmount'] += $order->getAmount();
            $record['totalProfit'] += $order->getProfit();

            $data[$key] = $record;
        }

        return $data;
    }

    #[Route("/orders/customer/{id}", name: "orders.customer", requirements: ["id" => "[0-9]+"])]
    public function customerOrdersAction(int $id): Response
    {
        $customer = $this->findOr404(Customer::class, $id);

        return $this->render('orders/index.twig', [
            'title' => 'Orders by ' . $customer->getName(),
            'customer' => $customer,
            'orders' => $this->getRepo(Order::class)->findBy(['customer' => $customer], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route("/orders/product/{id}", name: "orders.product", requirements: ["id" => "[0-9]+"])]
    public function productOrdersAction(int $id): Response
    {
        $product = $this->findOr404(Product::class, $id);

        return $this->render('orders/index.twig', [
            'title' => 'Orders of ' . $product->getTitle(),
            'orders' => $this->getRepo(Order::class)->findBy(['product' => $product], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route("/orders/{id}", name: "orders.edit", requirements: ["id" => "[0-9]+"])]
    #[Route("/orders/add", name: "orders.add")]
    public function editAction(?int $id): Response
    {
        if ($this->entity) {
            $entity = $this->entity;
        } elseif ($id) {
            $entity = $this->findOr404(Order::class, $id);
        } else {
            $entity = new Order();
            $entity->setQuantity(1);
        }

        $form = $this->createForm(OrderForm::class, $entity);

        return $this->render('orders/edit.twig', [
            'order' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/orders/save", name: "orders.save", methods: ["POST"])]
    public function saveAction(Request $request): Response
    {
        $id = intval($request->request->get('id'));
        $isEdit = $id > 0;
        $entity = $isEdit ? $this->findOr404(Order::class, $id) : new Order();

        $form = $this->createForm(OrderForm::class, $entity);

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

            return $this->redirectToRoute('orders.edit', ['id' => $entity->getId()]);
        } else {
            $this->addFlash('error', $this->getFormErrorsAsString($form));
            $this->entity = $entity;

            return $this->editAction($entity->getId());
        }
    }
}
