<?php

namespace App\Form;

use App\Entity\Attendance;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class AttendanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tag', null, [
		'label' => 'Tag',
                'constraints' => [
                    new NotBlank(),
                    new LessThanOrEqual(9999),
                ],
            ])
            ->add('patient', EntityType::class, [
		'label' => 'Patient',
                'class' => Patient::class,
                'choice_label' => 'name',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $attendance = $event->getData();
            $form = $event->getForm();

            if (!$attendance || null === $attendance->getId()) {
                // New attendance
                $form->add('checkInAt', DateTimeType::class, [
		    'label' => 'Check in',
                    'widget' => 'single_text',
                    'data' => new \DateTimeImmutable(),
                ]);
                $form->add('checkOutAt', DateTimeType::class, [
		    'label' => 'Check out',
                    'widget' => 'single_text',
                    'required' => false,
                ]);
            } else {
                // Existing attendance
                $form->add('checkInAt', DateTimeType::class, [
		    'label' => 'Check In',
                    'widget' => 'single_text',
                ]);

                $checkOutOptions = [
		    'label' => 'Check out',
                    'widget' => 'single_text',
                    'required' => false,
                ];

                if (null === $attendance->getCheckOutAt()) {
                    $checkOutOptions['data'] = new \DateTimeImmutable();
                }

                $form->add('checkOutAt', DateTimeType::class, $checkOutOptions);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Attendance::class,
        ]);
    }
}
