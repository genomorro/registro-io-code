<?php

namespace App\Form;

use App\Entity\Patient;
use App\Entity\Visitor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
        $builder
            ->add('name')
            ->add('phone', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'max' => 10]),
                    new Regex([
                        'pattern' => '/^[0-9]+$/',
                        'message' => 'Please enter a valid phone number.',
                    ]),
                ],
            ])
            ->add('dni')
            ->add('tag', null, [
                'constraints' => [
                    new NotBlank(),
                    new LessThanOrEqual(9999),
                ],
            ])
            ->add('destination')
            ->add('relationship')
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $visitor = $event->getData();
            $form = $event->getForm();

            if (!$visitor || null === $visitor->getId()) {
                // New visitor
                $form->add('checkInAt', DateTimeType::class, [
                    'widget' => 'single_text',
                    'data' => new \DateTimeImmutable(),
                ]);
                $form->add('checkOutAt', DateTimeType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                ]);
            } else {
                // Existing visitor
                $form->add('checkInAt', DateTimeType::class, [
                    'widget' => 'single_text',
                ]);

                $checkOutOptions = [
                    'widget' => 'single_text',
                    'required' => false,
                ];

                if (null === $visitor->getCheckOutAt()) {
                    $checkOutOptions['data'] = new \DateTimeImmutable();
                }

                $form->add('checkOutAt', DateTimeType::class, $checkOutOptions);
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
