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

class ExportPersonalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'label' => 'Выберите персону для выгрузки',
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
