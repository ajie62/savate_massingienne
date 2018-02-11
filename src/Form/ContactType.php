<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 11/02/2018
 * Time: 18:35
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('subject', TextType::class, [
                'label' => 'Objet',
                'required' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Message',
                'required' => true,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_contact';
    }
}