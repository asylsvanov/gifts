<?php

namespace App\Form;

use App\Entity\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use App\Form\PreferenceAutocompleteField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('author')
            ->add('size')
            ->add('price')
            ->add('originCountry', CountryType::class, [
                'autocomplete' => true
            ])
            ->add('category', ChoiceType::class, [
                'choices'  => [
                    'President' => 'CATEGORY_PRESIDENT',
                    'Prime Minister' => 'CATEGORY_PM',
                    'Protocol' => 'CATEGORY_PROTOCOL',
                ],
                'autocomplete' => false,
                // 'multiple' => true,
                'required' => true
            ])
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'For Male' => 'MALE',
                    'For Female' => 'FEMALE',
                    'No matter' => '',
                ],
                'autocomplete' => false,
                'required' => true
            ])
            ->add('generation', ChoiceType::class, [
                'choices'  => [
                    '20-30' => '20-30',
                    '30-40' => '30-40',
                    '40-50' => '40-50',
                    '50-60' => '50-60',
                    '60-70' => '60-70',
                    '70-80' => '70-80',
                    '80-90' => '80-90',
                ],
                'autocomplete' => false,
                'required' => true
            ])
            ->add('summary')
            ->add('isAvailable')
            ->add('daysToDelivery')
            ->add('counter')
            ->add('material')
            ->add('isActive')
            // ->add('preferences', PreferenceAutocompleteField::class)
            ->add('preferences', PreferenceAutocompleteField::class)
            ->add('photos', CollectionType::class, array(
                'entry_type' => PhotoType::class,
                'allow_add' => true,
                // 'label' => false,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                // 'prototype' => true,
                'entry_options' => [
                    'label' => false,
                ],
                // 'delete_empty' => function (Attachment $attachment = null) {
                //     return null === $attachment || empty($attachment->getImageName());
                // },
            ))
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
        ]);
    }
}
