<?php

namespace App\Form;

use App\Entity\Preference;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\PreferenceAutocompleteField;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Entity\Person;
use App\Repository\PersonRepository;

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'For Male' => 'MALE',
                    'For Female' => 'FEMALE',
                ],
                'label' => 'Для кого сувенир? *',
                'placeholder' => 'Выберите из списка',
                // 'help' => 'Указан в свойствах сувенира',
            ])
            ->add('preference', PreferenceAutocompleteField::class, [
                'label' => 'Что предпочитает? *',
                // 'help' => 'Указан в свойствах сувенира',
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'President' => 'CATEGORY_PRESIDENT',
                    'Prime Minister' => 'CATEGORY_PM',
                    'Protocol' => 'CATEGORY_PROTOCOL',
                ],
                // 'multiple' => false,
                'placeholder' => 'Не обязательно',
                'label' => 'Включить сувениры для определенной категории',
                // 'help' => 'Указан в свойствах сувенира',
                'required' => false,
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
                'label' => 'Выбрать сувениры для определенного возраста',
                'placeholder' => 'Не обязательно',
                // 'help' => 'Указан в свойствах сувенира',
                'autocomplete' => false,
                'required' => false
            ])
            ->add('ignore_counter', CheckboxType::class, [
                'label' => 'Включить отсутствующие на складе сувениры',
                'required' => false
            ])
            ->add('include_person', CheckboxType::class, [
                'label' => 'Включить биографию персоны',
                'required' => false
            ])
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'required' => false,
                'label' => 'Включить биографию в выгрузку',
                'placeholder' => 'Choose an option',
                'autocomplete' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
