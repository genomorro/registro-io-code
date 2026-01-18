<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
	    ->add('name', null, [
		'label' => 'Name',
	    ])
            ->add('username', null, [
		'label' => 'Username',
	    ]);

	$choices = [
	    'User' => 'ROLE_USER',
	];

	if ($this->security->isGranted('ROLE_ADMIN')) {
	    $choices['Admin'] = 'ROLE_ADMIN';
	}

	if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
	    $choices['Super Admin'] = 'ROLE_SUPER_ADMIN';
	}
	
	$builder->add('roles', ChoiceType::class, array(
		'label' => 'User roles',
		'autocomplete' => true,
		'choices'  => $choices,
		'multiple' => false,
		'required' => true,
	    ));

	$builder->get('roles')
	    ->addModelTransformer(new CallbackTransformer(
		function ($rolesAsArray): string {
		    if (!is_array($rolesAsArray)) {
			return 'ROLE_USER';
		    }
		    if (in_array('ROLE_SUPER_ADMIN', $rolesAsArray)) {
			return 'ROLE_SUPER_ADMIN';
		    }
		    if (in_array('ROLE_ADMIN', $rolesAsArray)) {
			return 'ROLE_ADMIN';
		    }
		    return 'ROLE_USER';
		},
		function ($roleAsString): array {
		    if ($roleAsString === 'ROLE_SUPER_ADMIN') {
			return ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
		    }
		    if ($roleAsString === 'ROLE_ADMIN') {
			return ['ROLE_USER', 'ROLE_ADMIN'];
		    }
		    return ['ROLE_USER'];
		}
	    ));
        /* ->add('agreeTerms', CheckboxType::class, [
	 *     'mapped' => false,
	 *     'constraints' => [
	 *         new IsTrue([
	 *             'message' => 'You should agree to our terms.',
	 *         ]),
	 *     ],
	 * ]) */
	
	$passwordConstraints = [
	    new Length(
		min: 8,
		minMessage: 'Your password should be at least {{ limit }} characters',
		// max length allowed by Symfony for security reasons
		max: 4096,
	    ),
	];

	if (!$options['is_edit']) {
	    $passwordConstraints[] = new NotBlank([
		'message' => 'Please enter a password',
	    ]);
	}

	$builder->add('plainPassword', RepeatedType::class, [
	    'type' => PasswordType::class,
	    'first_options'  => ['label' => 'Password', 'hash_property_path' => 'password'],
	    'second_options' => ['label' => 'Repeat Password'],
	    'mapped' => false,
	    'required' => !$options['is_edit'],
	    'constraints' => $passwordConstraints,
	]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
	    'is_edit' => false,
        ]);
    }
}
