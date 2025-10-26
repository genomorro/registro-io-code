<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
	    ->add('name', null, [
		'label' => 'Name',
	    ])
            ->add('username', null, [
		'label' => 'Username',
	    ])
	    ->add('roles', ChoiceType::class, array(
		'label' => 'User roles',
		'autocomplete' => true,
		'choices'  => [
		    'User' => 'ROLE_USER',
		    'Admin' => 'ROLE_ADMIN',
		    'Super Admin' => 'ROLE_SUPER_ADMIN',
		],
		'multiple' => true,
		'required' => true,
	    ))
        /* ->add('agreeTerms', CheckboxType::class, [
	 *     'mapped' => false,
	 *     'constraints' => [
	 *         new IsTrue([
	 *             'message' => 'You should agree to our terms.',
	 *         ]),
	 *     ],
	 * ]) */
	    ->add('plainPassword', RepeatedType::class, [
		'type' => PasswordType::class,
		'first_options'  => ['label' => 'Password', 'hash_property_path' => 'password'],
		'second_options' => ['label' => 'Repeat Password'],
		'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
	    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
