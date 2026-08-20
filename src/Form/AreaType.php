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
		    'Edificio 1 (Edificio de Gobierno)'  => '1',
		    'Edificio 2 (Kiosco)'  => '2',
		    'Edificio 3 (Antigua consulta externa)'  => '3',
		    'Edificio 4 (Enseñanza)'  => '4',
		    'Edificio 5 (Dirección médica)'  => '5',
		    'Edificio 6 (Auditorio Dr. M. Jiménez)'  => '6',
		    'Edificio 7 (Tanque de agua)'  => '7',
		    'Edificio 8 (Caja de cobro general)'  => '8',
		    'Edificio 9 (SC 1 y 2)'  => '9',
		    'Edificio 10 (SC 3 y 4)' => '10',
		    'Edificio 11 (SC 5 y 6)' => '11',
		    'Edificio 12 (SC 7 a 11)' => '12',
		    'Edificio 13 (Comedor)' => '13',
		    'Edificio 14 (Tabaquismo y EPOC)' => '14',
		    'Edificio 15 (Patología)' => '15',
		    'Edificio 16 (Almacén de desechos biológico-infecciosos)' => '16',
		    'Edificio 17 (Almacén)' => '17',
		    'Edificio 18 (Mantenimiento)' => '18',
		    'Edificio 19 (Bioterio)' => '19',
		    'Edificio 20 (Investigación)' => '20',
		    'Edificio 21 (Antigua unidad de investigación)' => '21',
		    'Edificio 22' => '22',
		    'Edificio 23 (CIENI)' => '23',
		    'Edificio 24 (Auditorio Dr. Rébora)' => '24',
		    'Edificio 25 (Pediatría)' => '25',
		    'Edificio 26 (Consulta Externa)' => '26',
		    'Edificio 27 (Comunicación social)' => '27',
		    'Edificio 28 (Laboratorio)' => '28',
		    'Edificio 29 (ENEO)' => '29',
		    'Edificio 30 (Auditorio Donato G. Alarcón)' => '30',
		    'Edificio 31 (Almacén de farmacia)' => '31',
		    'Edificio 32 (Aulas)' => '32',
		    'Edificio 33 (Cafetería)' => '33',
		    'Edificio 34 (Subestación de la Unidad de neumología pediátrica)' => '34',
		    'Edificio 35 (Subestación DIENI)' => '35',
		    'Edificio 36 (Transformador Imagenología)' => '36',
		    'Edificio 37 (Cardiología)' => '37',
		    'Edificio 38' => '38',
		    'Edificio 39' => '39',
		    'Edificio 40' => '40',
		    'Edificio 41' => '41',
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
