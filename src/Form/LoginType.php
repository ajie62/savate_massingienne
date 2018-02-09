<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 05/02/2018
 * Time: 12:46
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'Adresse mail',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
            ])
        ;
    }
}