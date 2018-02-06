<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 06/02/2018
 * Time: 13:42
 */

namespace App\Form;

use App\Entity\License;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $range = range(
            (new \DateTime())->format('Y'),
            (new \DateTime('-3YEARS'))->format('Y')
        );

        # Create an array by using $range for keys and $range for values
        $yearsRange = array_combine($range, $range);

        $builder
            ->add('licenseFile', FileType::class)
            ->add('year', ChoiceType::class, ['choices' => $yearsRange])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', License::class);
    }
}