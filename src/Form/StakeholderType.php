<?php

namespace App\Form;

use App\Entity\Stakeholder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('dni', null, [
		'label' => 'DNI',
	    ])
            ->add('tag')
            ->add('subject', ChoiceType::class, [
		'placeholder' => 'Choose a subject',
		'choices' => [
		    'Cultura' => 'Cultura',
		    'Empleado' => 'Empleado',
		    'Medio de comunicación' => 'Medio de comunicación',
		    'ONG' => 'ONG',
		    'Promotor' => 'Promotor',
		    'Proveedor' => 'Proveedor',
		],
		'tom_select_options' => [
		    'plugins' => [
			'remove_button' => true,
			'clear_button' => false,
		    ],
		],
		'autocomplete' => true,
	    ])
            ->add('destination', ChoiceType::class, [
		'placeholder' => 'Choose a destination',
		'choices' => [
		    'Consulta Externa' => 'Consulta Externa',
		    'Farmacia' => 'Farmacia',
		],
		'tom_select_options' => [
		    'plugins' => [
			'remove_button' => true,
			'clear_button' => false,
		    ],
		],
		'autocomplete' => true,
	    ])
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
