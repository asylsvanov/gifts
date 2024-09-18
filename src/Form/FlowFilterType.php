<?php

namespace App\Form;

use App\Entity\Flow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Person;
use App\Entity\Gift;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use App\Repository\GiftRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FlowFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('get')
            ->add('personFrom', EntityType::class, [
                'class' => Person::class,
                'placeholder' => 'Кто подарил',
                'autocomplete' => true,
                'label' => false,
                'required' => false
            ])
            ->add('personTo', EntityType::class, [
                'class' => Person::class,
                'placeholder' => 'Кому подарили',
                'autocomplete' => true,
                'label' => false,
                'required' => false
            ])
            ->add('gift', EntityType::class, [
                'class' => Gift::class,
                'placeholder' => 'Сувенир',
                'autocomplete' => true,
                'required' => false,
                'label' => false,

            ])
            ->add('country', CountryType::class, [
                'autocomplete' => true,
                'required' => false,
                'label' => false,
                'placeholder' => 'Страна',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }

    
}
