<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 15:42
 */

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom'
            ])
            ->add('startingDate', DateTimeType::class, [
                'required' => true,
                'label' => 'Date de début',
                'date_format' => 'dd/MM/yyyy',
                'years' => range(date('Y'), date('Y') + 5),
                'invalid_message' => "Valeur incorrecte"
            ])
            ->add('endingDate', DateTimeType::class, [
                'required' => true,
                'label' => 'Date de fin',
                'date_format' => 'dd/MM/yyyy',
                'years' => range(date('Y'), date('Y') + 5),
                'invalid_message' => "Valeur incorrecte"
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Description'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Event::class);
    }
}