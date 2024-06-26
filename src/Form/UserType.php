<?php

namespace App\Form;

use App\Entity\Medecin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('fullname')
            ->add('email')
            ->add('phone')
            ->add('password', RepeatedType::class,[
               'type'=>PasswordType::class,
               'first_options'=>['label'=>'Password'] ,
               'second_options'=>['label'=>'Confirm Password']
            ])
            ->add('photo', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false, // Set to true if the photo is mandatory
                // Add any other options you need, such as validation constraints
            ])
            ->add('Submit',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
        ]);
    }
}
