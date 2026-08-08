<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Restock;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

class RestockForm extends AbstractForm implements FormTypeInterface
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
                    'title' => 'Restock date',
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
                        . ' / ₴' . (int) $product->getBuyPriceUah()
                        . ' / ₴' . (int) $product->getSalePriceUah();
                },
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                    'title' => 'qty / buy / buy / sale / sale'
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
                'constraints' => [
                    new GreaterThan(['value' => 0]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-hryvnia',
                    'title' => 'Price',
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
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData']);
    }

    public function postSetData(FormEvent $event)
    {
        /** @var Restock $restock */
        $restock = $event->getData();

        $event->getForm()->get('amount')->setData($restock->getAmount());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Restock::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'id' => 'restock_edit_form',
                'v-form-unload-watcher' => '',
                'action' => $this->urlGenerator->generate('restocks.save'),
            ],
        ]);
    }
}
