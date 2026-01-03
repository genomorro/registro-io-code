<?php

namespace App\Form;

use App\Entity\Stakeholder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class StakeholderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $stakeholder = $options['data'] ?? null;
        $dniData = null;
        $dniOtherData = null;

        $dniChoices = [
            'INE' => 'INE',
            'Pasaporte' => 'Pasaporte',
            'Cédula profesional' => 'Cédula profesional',
            'Licencia de conducir' => 'Licencia de conducir',
            'INAPAM' => 'INAPAM',
            'Otro' => 'Otro',
        ];

        if ($stakeholder && $stakeholder->getDni() !== null && !in_array($stakeholder->getDni(), $dniChoices)) {
            $dniData = 'Otro';
            $dniOtherData = $stakeholder->getDni();
        } elseif ($stakeholder) {
            $dniData = $stakeholder->getDni();
        }

        $builder
            ->add('name')
            ->add('dni', ChoiceType::class, [
		'label' => 'DNI',
		'placeholder' => 'Choose a DNI',
		'choices' => $dniChoices,
		'mapped' => false,
		'data' => $dniData,
		'tom_select_options' => [
		    'plugins' => [
			'remove_button' => true,
			'clear_button' => false,
		    ],
		],
		'autocomplete' => true,
		'constraints' => [
                    new NotBlank(),
                ],
	    ])
            ->add('dni_other', TextType::class, [
                'label' => 'Other DNI',
                'mapped' => false,
                'required' => false,
                'data' => $dniOtherData,
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
	    ->add('evidence', HiddenType::class, [
		'mapped' => false,
	    ])
	    ->add('sign', HiddenType::class)
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

	$builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $stakeholder = $event->getData();

            $dni = $form->get('dni')->getData();
            if ($dni === 'Otro') {
                $dniOther = $form->get('dni_other')->getData();
                $stakeholder->setDni($dniOther);
            } else {
                $stakeholder->setDni($dni);
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
