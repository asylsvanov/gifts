<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MatchCountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', CountryType::class, [
                'autocomplete' => true
            ])
            ->add('category', ChoiceType::class, [
                'choices'  => [
                    'President' => 'CATEGORY_PRESIDENT',
                    'Prime Minister' => 'CATEGORY_PM',
                    'Protocol' => 'CATEGORY_PROTOCOL',
                    'Member of Congress' => 'CATEGORY_MOC',
                    'Senator' => 'CATEGORY_SENATOR',
                    'Minister' => 'CATEGORY_MINISTER',
                    'Speaker' => 'CATEGORY_SPEAKER',
                    'Member of parliament' => 'CATEGORY_MOP',
                    'Governor' => 'CATEGORY_GOVERNOR',
                    'Lieutenant Governor' => 'CATEGORY_LGOVERNOR',
                    'Chief Minister' => 'CATEGORY_CM',
                    'Mayor' => 'CATEGORY_MAYOR',
                    'Councillor' => 'CATEGORY_COUNCILLOR',
                ],
                'autocomplete' => true,
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
