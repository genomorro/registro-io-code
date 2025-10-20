<?php

namespace App\UXTable;

use App\Entity\Appointment;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WeDevelop\UXTable\Table\AbstractTable;

final class AppointmentTable extends AbstractTable
{
    public function getName(): string
    {
        return 'appointment';
    }

    protected function buildFilterForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'name',
                'attr' => $this->stimulusSearchAttributes(),
                'required' => false,
            ])
            ->add('place', SearchType::class, [
                'attr' => $this->stimulusSearchAttributes(),
                'required' => false,
            ])
        ;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'sortable_fields' => ['id', 'patient.name', 'place', 'dateAt', 'type'],
        ]);
    }
}
