<?php

namespace App\Form;

use App\Entity\Person;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\AttachmentType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\PreferenceAutocompleteField;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prefix')
            ->add('firstName')
            ->add('lastName')
            ->add('surname', TextType::class, [
                'required' => false
            ])
            ->add('age', TextType::class, [
                'required' => false
            ])
            ->add('sex', ChoiceType::class, [
                'choices'  => [
                    'Male' => 1,
                    'Female' => 2,
                ]
            ])
            ->add('birthAt', BirthdayType::class, [
                'input' => 'datetime_immutable'
            ])
            ->add('country', CountryType::class, [
                'autocomplete' => true,
                'placeholder' => 'Choose an option',
            ])
            ->add('language', LanguageType::class, [
                'autocomplete' => true,
                'multiple' => true
            ])
            ->add('summary', CKEditorType::class)
            // ->add('summary', TextareaType::class)
            // ->add('isActive')
            ->add('socialProfiles', CollectionType::class, [
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                // 'prototype' => true,
            ])
            ->add('category', ChoiceType::class, [
                'choices'  => [
                    'President' => 'CATEGORY_PRESIDENT',
                    'Prime Minister' => 'CATEGORY_PM',
                    'Protocol' => 'CATEGORY_PROTOCOL',
                ],
                'autocomplete' => true,
                'multiple' => true
            ])
            ->add('preferences', PreferenceAutocompleteField::class)
            ->add('attachments', CollectionType::class, array(
                'entry_type' => AttachmentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'prototype' => true,
                'entry_options' => [
                    'label' => false,
                ],
                // 'delete_empty' => function (Attachment $attachment = null) {
                //     return null === $attachment || empty($attachment->getImageName());
                // },
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
