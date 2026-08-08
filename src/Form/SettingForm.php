<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Setting;
use App\Enum\ValueType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SettingForm extends AbstractForm implements FormTypeInterface
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
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Name',
                    'title' => 'Name',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-font',
                    'title' => 'Name',
                ],
            ])
            ->add('value', TextType::class, [
                'label' => ' ',
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Value',
                    'title' => 'Value',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-9',
                    'title' => 'Value',
                ],
            ])
            ->add('description', TextType::class, [
                'label' => ' ',
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description',
                    'title' => 'Description',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-align-justify',
                    'title' => 'Description',
                ],
            ])
            ->add('type', EnumType::class, [
                'label' => ' ',
                'required' => true,
                'class' => ValueType::class,
                'attr' => [
                    'class' => 'form-select',
                    'title' => 'Type',
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-file-circle-question',
                    'title' => 'Type',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'id' => 'setting_edit_form',
                'v-form-unload-watcher' => '',
                'action' => $this->urlGenerator->generate('settings.save'),
            ]
        ]);
    }
}
