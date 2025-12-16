<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agenda', null, [
		'label' => 'Agenda',
	    ])
	    ->add('specialty', null, [
		'label' => 'Specialty',
	    ])
	    ->add('location', null, [
		'label' => 'Location',
	    ])
            ->add('date_at', null, [
		'label' => 'Date at',
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
		'label' => 'Type',
		'placeholder' => 'Choose a type of appointment',
                'choices' => [
		    'Apertura/Primera vez' => 'Apertura/primera vez',
		    'Estudios' => 'Estudios',
		    'Preconsulta' => 'Preconsulta',
		    'Primera vez y subsecuente' => 'Primera vez y subsecuente', 
		    'Primera vez' => 'Primera vez',
		    'Subsecuente' => 'Subsecuente',
                ],
		'autocomplete' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
	    ->add('status', ChoiceType::class, [
		'label' => 'Status',
		'placeholder' => 'Choose a status',
		'choices' => [
		    'Agendada' => 'Agendada',
		    'Cancelada' => 'Cancelada',
		],
		'autocomplete' => true,
		'constraints' => [
		    new NotBlank(),
		],
	    ])
            ->add('patient', PatientAutocompleteField::Class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
