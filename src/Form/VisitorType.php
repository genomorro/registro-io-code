<?php

namespace App\Form;

use App\Entity\Patient;
use App\Entity\Visitor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class VisitorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $visitor = $options['data'] ?? null;
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

        if ($visitor && $visitor->getDni() !== null && !in_array($visitor->getDni(), $dniChoices)) {
            $dniData = 'Otro';
            $dniOtherData = $visitor->getDni();
        } elseif ($visitor) {
            $dniData = $visitor->getDni();
        }

        $builder
            ->add('name', null, [
		'label' => 'Name',
	    ])
            ->add('phone', null, [
		'label' => 'Phone number',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 10, max: 10),
                    new Regex(
                        pattern: '/^[0-9]+$/',
                        message: 'Please enter a valid phone number.',
                    ),
                ],
            ])
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
            ->add('tag', null, [
		'label' => 'Tag',
                'constraints' => [
                    new NotBlank(),
                    new LessThanOrEqual(9999),
                ],
            ])
            ->add('destination', ChoiceType::class, [
		'label' => 'Destination',
		'placeholder' => 'Choose a destination',
		'choices' => [
		    'Consulta Externa' => 'Consulta Externa',
		    'CIENI' => 'CIENI',
		    'Clínica del Asma' => 'Clínica del Asma',
		    'Clínica de EPOC' => 'Clínica de EPOC',
		    'Unidad de Urgencias Respiratorias' => 'Unidad de Urgencias Respiratorias',
		    'Hospitalización' => [
			'Servicio Clínico 1' => 'Servicio Clínico 1',
			'Servicio Clínico 2' => 'Servicio Clínico 2',
			'Servicio Clínico 3' => 'Servicio Clínico 3',
			'Servicio Clínico 4' => 'Servicio Clínico 4',
			'Servicio Clínico 5' => 'Servicio Clínico 5',
			'Hospital de día' => 'Hospital de día',
			'Unidad de Terapia Intermedia' => 'Unidad de Terapia Intermedia',
			'Nefrología' => 'Nefrología',
			'Oncología' => 'Oncología',
			'Broncoscopia Intervencionista' => 'Broncoscopia Intervencionista',
			'Broncoscopia' => 'Broncoscopia',
			'Servicio Clínico 7' => 'Servicio Clínico 7',
			'Neumología Pediátrica Ambulatoria' => 'Neumología Pediátrica Ambulatoria',
			'Unidad de Terapia Intensiva Pediátrica' => 'Unidad de Terapia Intensiva Pediátrica',
			'Servicio Clínico 8' => 'Servicio Clínico 8',
			'Servicio Clínico 9' => 'Servicio Clínico 9',
			'Servicio Clínico 10 Postquirúrgicos' => 'Servicio Clínico 10 Postquirúrgicos',
			'Servicio Clínico 10 Recuperación' => 'Servicio Clínico 10 Recuperación',
		    ],
		],
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
            ->add('relationship', ChoiceType::class, [
		'label' => 'Relationship',
		'placeholder' => 'Choose a relationship',
		'choices' => [
		    'Paciente' => 'Paciente',
		    'Padre' => 'Padre',
		    'Madre' => 'Madre',
		    'Hijo (a)' => 'Hijo (a)',
		    'Cónyuge' => 'Cónyuge',
		    'Concubino (a)' => 'Concubino (a)',
		    'Hermano (a)' => 'Hermano (a)',
		    'Otro' => 'Otro',
		],
		'tom_select_options' => [
		    'plugins' => [
			'remove_button' => true,
			'clear_button' => false,
		    ],
		],
		'autocomplete' => true,
		'required' => false,
	    ])
            ->add('patient', PatientAutocompleteField::class, [
		'extra_options' => [
		    'multiple' => true,
		    'required' => false,
                ],
            ])
	    ->add('evidence', HiddenType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $visitor = $event->getData();
            $form = $event->getForm();

            if (!$visitor || null === $visitor->getId()) {
                // New visitor
                $form->add('checkInAt', DateTimeType::class, [
		    'label' => 'Check in',
                    'widget' => 'single_text',
                    'data' => new \DateTimeImmutable(),
                ]);
            } else {
                // Existing visitor
                $form->add('checkInAt', DateTimeType::class, [
		    'label' => 'Check in',
                    'widget' => 'single_text',
                ]);

                $checkOutOptions = [
		    'label' => 'Check out',
                    'widget' => 'single_text',
                    'required' => false,
                ];

                if (null === $visitor->getCheckOutAt()) {
                    $checkOutOptions['data'] = new \DateTimeImmutable();
                }

                $form->add('checkOutAt', DateTimeType::class, $checkOutOptions);
            }
        });

	$builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $visitor = $event->getData();

            $dni = $form->get('dni')->getData();
            if ($dni === 'Otro') {
                $dniOther = $form->get('dni_other')->getData();
                $visitor->setDni($dniOther);
            } else {
                $visitor->setDni($dni);
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
