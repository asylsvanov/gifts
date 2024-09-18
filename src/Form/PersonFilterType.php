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

class PersonFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->setMethod('get')
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('sex', ChoiceType::class, [
                'choices'  => [
                    'Male' => 1,
                    'Female' => 2,
                ],
                'required' => false
            ])
            
            ->add('country', CountryType::class, [
                'autocomplete' => true,
                'placeholder' => 'Choose an option',
                'required' => false
            ])
            // ->add('language', LanguageType::class, [
            //     'autocomplete' => true,
            //     // 'multiple' => true,
            //     'required' => false
            // ])
            // ->add('category', ChoiceType::class, [
            //     'choices'  => [
            //         'President' => 'CATEGORY_PRESIDENT',
            //         'Prime Minister' => 'CATEGORY_PM',
            //         'Protocol' => 'CATEGORY_PROTOCOL',
            //     ],
            //     'autocomplete' => true,
            //     // 'multiple' => true,
            //     'required' => false
            // ])
            ->add('preferences', PreferenceAutocompleteField::class,[
                'required' => false
            ]
            )
        ;
    }

}
