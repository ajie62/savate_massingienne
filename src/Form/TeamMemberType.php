<?php

namespace App\Form;

use App\Entity\TeamMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('job', TextType::class, [
                'label' => 'Rôle',
                'required' => true,
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'Photo',
                'required' => false,
            ])
            ->add('deleteButton', ButtonType::class, [
                'label' => 'Retirer',
                'attr' => ['class' => 'js-delete-team-member'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', TeamMember::class);
    }
}