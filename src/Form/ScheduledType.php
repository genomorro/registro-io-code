<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Scheduled;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScheduledType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('name')
            ->add('institution')
            ->add('subject', ChoiceType::class, [
		'placeholder' => 'Choose a subject',
		'choices' => [
		    'Cultura' => 'Cultura',
		    'Documentación' => 'Documentación',
		    'Escuela' => 'Escuela',
		    'Estudiante' => 'Estudiante',
		    'Expositor' => 'Expositor',
		    'Medio de comunicación' => 'Medio de comunicación',
		    'Proveedor' => 'Proveedor',
		    'Trabajo' => 'Trabajo',
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
            ->add('beginAt', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('endAt', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('area', AreaAutocompleteField::class, [
                'label' => 'Area',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Scheduled::class,
        ]);
    }
}
