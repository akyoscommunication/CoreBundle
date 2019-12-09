<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\Menu;
use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Repository\MenuRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'help' => 'Donnez un titre à votre menu !',
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'help' => 'Courte description de la zone (où est-elle située sur le site ?)',
            ])
            ->add('menu', EntityType::class, [
                'label' => 'Menu',
                'help' => 'Souhaitez-vous associer un menu à cette zone ?',
                'required' => false,
                'class' => Menu::class,
                'query_builder' => function (MenuRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.title', 'ASC');
                },
                'choice_label' => 'title',
                'placeholder' => "Choisissez un menu"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuArea::class,
        ]);
    }
}
