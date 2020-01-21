<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\User;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = User::ROLES;
        unset($roles['Akyos']);

        $builder
            ->add('email', EmailType::class, [
                'label' => "E-mail",
                'help' => "Renseignez l'email de l'utilisateur"
            ])
            ->add('roles', ChoiceType::class, [
                'label' => "Rôle de l'utilisateur",
                'help' => "En fonction de son rôle, l'utilisateur aura accès à différentes fonctionnalités.",
                'choices' => $roles,
                'multiple' => true,
                'expanded' => false,
                'required' => true,
            ])
            ->add('image', FileManagerType::class, [
                'label' => 'Image de profil',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}