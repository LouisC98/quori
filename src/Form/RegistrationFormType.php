<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'row_attr' => ['class' => 'email']
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe :',
                'row_attr' => ['class' => 'password']
            ])
            ->add('name', TextType::class, [
                'label' => 'Prénom :',
                'row_attr' => ['class' => 'name']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom :',
                'row_attr' => ['class' => 'lastname']
            ])
            ->add('picture', FileType::class, [
                'label' => 'Image de profil :',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'picture'],
                'constraints' => [
                    new Image([
                        'mimeTypesMessage' => 'Ce fichier n\'est pas une image valide',
                        'maxSize' => '1M',
                        'maxSizeMessage' => 'Votre image est trop volumineuse (max : 1Mo)',
                        'maxRatio' => 1,
                        'maxRatioMessage' => 'Le ratio de l\'image  est trop grand ({{ ratio }}). Ratio max {{ max_ratio }} (carré).'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
