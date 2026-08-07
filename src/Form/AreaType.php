<?php

namespace App\Form;

use App\Entity\Area;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('building', ChoiceType::class, [
		'label' => 'Building',
		'placeholder' => 'Choose a building',
		'choices' => [
		    'Edificio 3 (Consulta externa antigua)' => '3',
		    'Edificio 5' => '5',
		    'Edificio 9 (Servicio Clínico 1 y 2)' => '9',
		    'Edificio 10 (Servicio Clínico 3 y 4)' => '10',
		    'Edificio 11 (Servicio Clínico 5 y 6)' => '11',
		    'Edificio 12 ' => '12',
		    'Edificio 14' => '14',
		    'Edificio 15' => '15',
		    'Edificio 20' => '20',
		    'Edificio 21' => '21',
		    'Edificio 25' => '25',
		    'Edificio 26 (Consulta externa)' => '26',
		    'Edificio 27 (Unidad de Urgencias)' => '27',
		    'Edificio 28 (Laboratorios)' => '28',
		    'Edificio 37' => '37',
		    'Edificio 40 (Anexo de consulta externa)' => '40',
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
            ->add('unit', null, [
		'label' => 'Unit',
	    ])
            ->add('extension', null, [
		'label' => 'Extension',
		'constraints' => [
		    new Length(min: 4, max: 5),
		],
	    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
        ]);
    }
}
