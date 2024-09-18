<?php

namespace App\Form;

use App\Entity\Preference;
use App\Repository\PreferenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class PreferenceAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Preference::class,
            'placeholder' => 'Choose a Preference',
            //'choice_label' => 'name',
            'query_builder' => function(PreferenceRepository $preferenceRepository) {
                return $preferenceRepository->createQueryBuilder('preference');
            },
            // 'searchable_fields' => ['name'],
            'security' => 'ROLE_USER',
            'multiple' => true,
            'preload' => true,
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}
