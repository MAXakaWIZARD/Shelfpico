<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Type;

class OrderForm extends AbstractForm implements FormTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('id', HiddenType::class, [
                'required' => false,
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => ' ',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new Type(['type' => '\DateTimeInterface']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD H:I:S',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-clock',
                    'title' => 'Order date',
                ],
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.name', 'ASC')
                    ;
                },
                'choice_label' => function (Customer $customer) {
                    return $customer->getName();
                },
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-user',
                    'title' => 'Customer',
                ],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.title', 'ASC')
                    ;
                },
                'choice_label' => function (Product $product) {
                    return $product->getTitle()
                        . ' ' . $product->getSku()
                        . ' / ' . $product->getQuantity() . 'pcs'
                        . ' / ₴' . (int) $product->getLastBuyPriceUah()
                        . ' / ₴' . (int) $product->getBuyPriceUah()
                        . ' / ₴' . (int) $product->getSalePriceUah();
                },
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                    'title' => 'qty / last buy / buy / sale'
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-cube',
                    'title' => 'Product',
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => ' ',
                'required' => true,
                'empty_data' => '0',
                'constraints' => [
                    new GreaterThan(['value' => 0]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Qty',
                    'title' => 'Quantity',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-th',
                    'title' => 'Quantity',
                ],
            ])
            ->add('price', IntegerType::class, [
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-arrow-up',
                    'title' => 'Sale price',
                ],
            ])
            ->add('buyPrice', IntegerType::class, [
                'label' => ' ',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-arrow-down',
                    'title' => 'Buy price',
                ],
            ])
            ->add('profit', IntegerType::class, [
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'disabled' => true,
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-filter',
                    'title' => 'Profit',
                ],
            ])
            ->add('amount', IntegerType::class, [
                'label' => ' ',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'disabled' => true,
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-hryvnia',
                    'title' => 'Amount',
                ],
            ])
            ->add('note', TextType::class, [
                'label' => ' ',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Note',
                    'title' => 'Note',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-list-alt',
                    'title' => 'Note',
                ],
            ])
            ->add('url', TextType::class, [
                'label' => ' ',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'URL',
                    'title' => 'URL',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-globe',
                    'title' => 'URL',
                ],
            ])
            ->add('paid', CheckboxType::class, [
                'label' => 'paid',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('shipped', CheckboxType::class, [
                'label' => 'shipped',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData']);
    }

    public function preSubmit(FormEvent $event)
    {
        /** @var array $data */
        $data = $event->getData();

        if ((int) $data['buyPrice'] > 0 && (int) $data['price'] > 0) {
            $data['profit'] = ($data['price'] - $data['buyPrice']) * $data['quantity'];
        } elseif ((int) $data['buyPrice'] > 0 && (int) $data['profit'] == 0) {
            $data['profit'] = ($data['price'] - $data['buyPrice']) * $data['quantity'];
        } elseif ((int) $data['price'] > 0 && (int) $data['profit'] > 0) {
            $data['buyPrice'] = ($data['price'] * $data['quantity']) - $data['profit'];
        } elseif ((int) $data['buyPrice'] > 0 && (int) $data['profit'] > 0) {
            $data['price'] = (($data['buyPrice'] * $data['quantity']) + $data['profit']) / $data['quantity'];
        }

        $event->setData($data);
    }

    public function postSetData(FormEvent $event)
    {
        /** @var Order $order */
        $order = $event->getData();

        $buyPrice = $order->getQuantity()
            ? ($order->getAmount() - $order->getProfit()) / $order->getQuantity()
            : 0;

        $event->getForm()->get('buyPrice')->setData($buyPrice);

        $event->getForm()->get('amount')->setData($order->getAmount());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'id' => 'order_edit_form',
                'v-form-unload-watcher' => '',
                'action' => $this->urlGenerator->generate('orders.save'),
            ],
        ]);
    }
}
