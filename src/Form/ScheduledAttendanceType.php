<?php

namespace App\Form;

use App\Entity\Scheduled;
use App\Entity\ScheduledAttendance;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduledAttendanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('checkInAt', DateTimeType::class, [
		'label' => 'Check in',
                'widget' => 'single_text',
                'data' => new \DateTimeImmutable(),
		'time_label' => 'Starts On',
            ])
            ->add('scheduled', ScheduledAutocompleteField::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScheduledAttendance::class,
        ]);
    }
}
