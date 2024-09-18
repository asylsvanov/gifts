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

class MatchCustomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'For Male' => 'MALE',
                    'For Female' => 'FEMALE',
                ],
                'label' => 'Для кого сувенир',
                'help' => 'Указан в свойствах сувенира',
                'required' => false
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
                'label' => 'Для какого возраста',
                'help' => 'Указан в свойствах сувенира',
                'autocomplete' => false,
                'required' => false
            ])
            ->add('preference', PreferenceAutocompleteField::class, [
                'required' => false,
                'label' => 'Предпочтения ',
                'help' => 'Указан в свойствах сувенира',
            ])
            ->add('category', ChoiceType::class, [
                'choices'  => [
                    'President' => 'CATEGORY_PRESIDENT',
                    'Prime Minister' => 'CATEGORY_PM',
                    'Protocol' => 'CATEGORY_PROTOCOL',
                ],
                // 'multiple' => false,
                'placeholder' => 'Choose an option',
                'required' => false,
                'label' => 'Категория лиц указанная в свойствах сувениров',
                'help' => 'Указан в свойствах сувенира',
            ])
            ->add('country', CountryType::class, [
                'autocomplete' => true,
                'required' => false,
                'label' => 'Страна персоны',
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
