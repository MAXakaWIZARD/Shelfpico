<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Product;
use App\Form\Traits\Taggable;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class ProductForm extends AbstractForm implements FormTypeInterface
{
    use Taggable;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('id', HiddenType::class, [
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => ' ',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Title',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-font',
                    'title' => 'Title',
                ],
            ])
            ->add('sku', TextType::class, [
                'label' => ' ',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'SKU',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-certificate',
                    'title' => 'SKU',
                ],
            ])
            ->add('supplierSalePriceUah', IntegerType::class, [
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
                    'title' => 'Sale price',
                ],
            ])
            ->add('popular', CheckboxType::class, [
                'label' => 'popular',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => ' ',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-image',
                ],
            ])
        ;

        $this->addUrlsFields($builder);
        $this->addTagsFields($builder);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    private function addUrlsFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('url', TextType::class, [
                'label' => ' ',
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                    new Url(),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Manufacturer URL',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-link',
                    'title' => 'Manufacturer URL',
                ],
            ])
            ->add('atomicUrl', TextType::class, [
                'label' => ' ',
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                    new Url(),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Supplier URL',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-cube',
                    'title' => 'Supplier URL',
                ],
            ])
        ;
    }

    public function preSubmit(FormEvent $event)
    {
        /** @var array $model */
        $data = $event->getData();

        $data['tags'] = $this->processTags($data);

        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'id' => 'product_edit_form',
                'v-form-unload-watcher' => '',
                'action' => $this->urlGenerator->generate('products.save'),
            ],
        ]);
    }
}
