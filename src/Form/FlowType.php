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
use App\Repository\GiftRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FlowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('personFrom', EntityType::class, [
                'class' => Person::class,
                'placeholder' => 'Choose an option',
                'autocomplete' => true,
                'required' => true
            ])
            ->add('newPersonFrom', PersonType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Or create a new person from',
            ])
            ->add('personTo', EntityType::class, [
                'class' => Person::class,
                'placeholder' => 'Choose an option',
                'autocomplete' => true,
                'required' => true
            ])
            ->add('newPersonTo', PersonType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Or create a new person to',
            ])
            ->add('gift', EntityType::class, [
                'class' => Gift::class,
                'placeholder' => 'Choose an option',
                'autocomplete' => true,
                'query_builder' => function (GiftRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->where('g.isAvailable = 1')
                        ->where('g.isActive = 1')
                        ->where('g.counter > 0')
                    ;
                },
                'required' => true
            ])
            ->add('newGift', GiftType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Or create a new gift',
            ])
            ->add('receivedAt', DateType::class, [
                'years' => range(1990,date('Y'))
            ])
            ->add('description', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flow::class,
        ]);
    }
}
