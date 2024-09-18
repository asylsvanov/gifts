<?php

namespace App\Form;

use App\Entity\Gift;
use App\Entity\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use App\Form\PreferenceAutocompleteField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class GiftFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'For Male' => 'MALE',
                    'For Female' => 'FEMALE',
                ],
                'placeholder' => 'Пол',
                'required' => false,
                'label' => false,
                // 'help' => 'Указан в свойствах сувенира',
            ])
            // ->add('preference', PreferenceAutocompleteField::class, [
            //     'placeholder' => 'Предпочтения',
            //     'required' => false,
            //     'label' => false,
            //     // 'help' => 'Указан в свойствах сувенира',
            // ])
            ->add('preference', EntityType::class, [
                'class' => Preference::class,
                'placeholder' => 'Choose an option',
                'attr' => [
                    'placeholder' => 'Предпочтение... ',
                    'style' => 'min-width: 174px !important;'
                ],
                'autocomplete' => true,
                'label' => false,
                // 'security' => 'ROLE_USER',
                'multiple' => true,
                'required' => false,
            'preload' => true,

                // 'required' => true
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'President' => 'CATEGORY_PRESIDENT',
                    'Prime Minister' => 'CATEGORY_PM',
                    'Protocol' => 'CATEGORY_PROTOCOL',
                ],
                // 'multiple' => false,
                'placeholder' => 'Категория',
                // 'help' => 'Указан в свойствах сувенира',
                'required' => false,
                'label' => false,
            ])
            ->add('generation', ChoiceType::class, [
                'choices' => [
                    '20-30' => '20-30',
                    '30-40' => '30-40',
                    '40-50' => '40-50',
                    '50-60' => '50-60',
                    '60-70' => '60-70',
                    '70-80' => '70-80',
                    '80-90' => '80-90',
                ],
                'placeholder' => 'Возраст',
                'autocomplete' => false,
                'required' => false,
                'label' => false,
            ])
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
