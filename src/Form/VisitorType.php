<?php

namespace App\Form;

use App\Entity\Patient;
use App\Entity\Visitor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
            ->add('relationship')
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $visitor = $event->getData();
            $form = $event->getForm();

            if ($visitor && null !== $visitor->getId()) {
                $form->add('checkInAt', null, [
                    'widget' => 'single_text',
                ]);
                $form->add('CheckOutAt', null, [
                    'widget' => 'single_text',
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visitor::class,
        ]);
    }
}
