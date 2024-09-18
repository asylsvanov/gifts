<?php

namespace App\Form;

use App\Entity\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('summary')
            // ->add('slug')
            // ->add('createdBy')
            // ->add('updatedBy')
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('deletedAt')
            // ->add('parent')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Preference::class,
        ]);
    }
}
