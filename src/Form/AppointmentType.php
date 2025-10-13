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
            ->add('place', null, [
		'label' => 'Place',
	    ])
            ->add('date_at', null, [
		'label' => 'Date at',
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
		'label' => 'Type',
                'choices' => [
                    'Estudio' => 'Estudio',
                    'Consulta' => 'Consulta',
                    'Procedimiento' => 'Procedimiento',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('patient', EntityType::class, [
		'label' => 'Patient',
                'class' => Patient::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
