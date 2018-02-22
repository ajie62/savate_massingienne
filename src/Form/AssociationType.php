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
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'attr' => ['placeholder' => 'L\'historique de l\'association...']
            ])
            ->add('textInfo', TextareaType::class, [
                'label' => 'Informations',
                'required' => false,
                'attr' => ['placeholder' => 'Les horaires et jours d\'entraînement, etc...']
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '01 23 45 67 89']
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
                'attr' => ['placeholder' => 'Décrivez brièvement le but de l\'association']
            ])
            ->add('facebookLink', UrlType::class, [
                'label' => 'Page facebook',
                'required' => false,
                'attr' => ['placeholder' => 'http://']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Association::class);
    }
}