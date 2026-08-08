<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Tag;
use App\Enum\LabelClass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TagForm extends AbstractForm implements FormTypeInterface
{
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
                'empty_data' => '',
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
            ->add('color', EnumType::class, [
                'label' => ' ',
                'required' => true,
                'class' => LabelClass::class,
                'attr' => [
                    'class' => 'form-select'
                ],
                'label_attr' => [
                    'class' => 'input-group-text fas fa-palette',
                    'title' => 'Color'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'id' => 'tag_edit_form',
                'v-form-unload-watcher' => '',
                'action' => $this->urlGenerator->generate('tags.save'),
            ]
        ]);
    }
}
