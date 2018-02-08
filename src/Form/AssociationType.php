<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 21:02
 */

namespace App\Form;

use App\Entity\Association;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('textIntro', TextareaType::class, [
                'label' => 'Introduction',
                'required' => false,
            ])
            ->add('textInfo', TextareaType::class, [
                'label' => 'Informations',
                'required' => false,
            ])
            ->add('teamMembers', CollectionType::class, [
                'label' => 'Membres d\'équipe',
                'required' => false,
                'entry_type' => TeamMemberType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'attr' => ['class' => 'team-member']
                ]
            ])
            ->add('addTeamMemberButton', ButtonType::class, [
                'label' => 'Ajouter',
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('aboutUs', TextareaType::class, [
                'label' => 'À propos',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Association::class);
    }
}