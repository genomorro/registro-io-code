<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Scheduled;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScheduledImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateTransformer = new CallbackTransformer(
            fn ($value) => $value,
            fn ($value) => $value instanceof \DateTimeInterface ? $value : ($value ? new \DateTimeImmutable($value) : null)
        );

        $builder
            ->add('label', TextType::class)
            ->add('name', TextType::class)
            ->add('institution', TextType::class)
            ->add('subject', ChoiceType::class, [
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
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('beginAt', TextType::class)
            ->add('endAt', TextType::class)
            ->add('area', EntityType::class, [
                'class' => Area::class,
            ]);

        $builder->get('beginAt')->addModelTransformer($dateTransformer);
        $builder->get('endAt')->addModelTransformer($dateTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Scheduled::class,
        ]);
    }
}
