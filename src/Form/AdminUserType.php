<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 05/02/2018
 * Time: 19:47
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityChecker = $options['securityChecker'];
        $activeUser = $options['user'];
        $targetUser = $options['data'];

        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom'
            ])
        ;

        # If the user's role is ROLE_SUPER_ADMIN || the targeted user is the active user
        if ($securityChecker->isGranted('ROLE_SUPER_ADMIN') || $targetUser == $activeUser) {
            # Add two new fields: licenseNumber & email
            $builder
                ->add('licenseNumber', TextType::class, [
                    'label' => 'N° de licence',
                    'required' => false,
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Adresse mail'
                ])
            ;
        }

        # If the user's role is ROLE_SUPER_ADMIN && the target user is different from the active user
        if ($securityChecker->isGranted('ROLE_SUPER_ADMIN') && $activeUser !== $targetUser) {
            # Add a roles field to the form
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Modérateur' => 'ROLE_MODERATEUR',
                    'Administrateur' => 'ROLE_SUPER_ADMIN'
                ],
                'data' => $targetUser->getRoles()[0] ?? null,
                'mapped' => false,
                'preferred_choices' => [
                    'Utilisateur' => 'ROLE_USER'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', User::class);
        $resolver->setDefault('securityChecker', null);
        $resolver->setDefault('user', null);
        $resolver->setRequired('securityChecker');
        $resolver->setRequired('user');
    }
}