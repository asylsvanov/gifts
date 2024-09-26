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
                'required' => false
            ])
            ->add('newPersonFrom', PersonType::class, [
                'required' => false,
            ])
            ->add('personTo', EntityType::class, [
                'class' => Person::class,
                'placeholder' => 'Choose an option',
                'autocomplete' => true,
                'required' => false
            ])
            ->add('newPersonTo', PersonType::class, [
                'required' => false,
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
                'required' => false
            ])
            ->add('newGift', GiftType::class, [
                'required' => false,
            ])
            ->add('receivedAt', DateType::class, [
                'data' => new \DateTime(), 
                'years' => range(1990,date('Y'))
            ])
            ->add('description', TextareaType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flow::class,
        ]);
    }
}
