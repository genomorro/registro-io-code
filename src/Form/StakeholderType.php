<?php

namespace App\Form;

use App\Entity\Stakeholder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StakeholderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('dni')
            ->add('tag')
            ->add('subject')
            ->add('destination')
            ->add('evidence')
            ->add('sign')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $stakeholder = $event->getData();
            $form = $event->getForm();

            if (!$stakeholder || null === $stakeholder->getId()) {
                // New stakeholder
                $form->add('checkInAt', DateTimeType::class, [
		    'label' => 'Check in',
                    'widget' => 'single_text',
                    'data' => new \DateTimeImmutable(),
                ]);
            } else {
                // Existing stakeholder
                $form->add('checkInAt', DateTimeType::class, [
		    'label' => 'Check in',
                    'widget' => 'single_text',
                ]);

                $checkOutOptions = [
		    'label' => 'Check out',
                    'widget' => 'single_text',
                    'required' => false,
                ];

                if (null === $stakeholder->getCheckOutAt()) {
                    $checkOutOptions['data'] = new \DateTimeImmutable();
                }

                $form->add('checkOutAt', DateTimeType::class, $checkOutOptions);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stakeholder::class,
        ]);
    }
}
