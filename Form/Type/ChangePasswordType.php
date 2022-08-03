<?php

namespace Akyos\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, ['type' => PasswordType::class, 'invalid_message' => 'Les deux valeurs doivent être identiques', 'options' => ['attr' => ['class' => 'password-field']], 'required' => true]);
    }
}
