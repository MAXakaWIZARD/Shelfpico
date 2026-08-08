<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerForm extends AbstractForm implements FormTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('id', HiddenType::class, [
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => ' ',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-font',
                    'title' => 'Name',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => ' ',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-phone',
                    'title' => 'Phone',
                ],
            ])
            ->add('shipmentInfo', TextType::class, [
                'label' => ' ',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-truck',
                    'title' => 'Shipment info',
                ],
            ])
            ->add('deposit', IntegerType::class, [
                'label' => ' ',
                'required' => true,
                'empty_data' => '0',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Deposit',
                    'title' => 'Deposit',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-dollar-sign',
                    'title' => 'Deposit',
                ],
            ])
            ->add('debt', IntegerType::class, [
                'label' => ' ',
                'required' => true,
                'empty_data' => '0',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Debt',
                    'title' => 'Debt',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-exclamation-circle',
                    'title' => 'Debt',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'id' => 'customer_edit_form',
                'v-form-unload-watcher' => '',
                'action' => $this->urlGenerator->generate('customers.save'),
            ],
        ]);
    }
}
