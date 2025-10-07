<?php

namespace App\Form;

use App\Entity\Patient;
use App\Entity\Visitor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisitorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('phone')
            ->add('dni')
            ->add('tag')
            ->add('destination')
            ->add('checkInAt', null, [
                'widget' => 'single_text',
            ])
            ->add('CheckOutAt', null, [
                'widget' => 'single_text',
            ])
            ->add('relationship')
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visitor::class,
        ]);
    }
}
