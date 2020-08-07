<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    protected $menu;
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->menu = $options['menu'];

        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'help' => 'Insérez votre titre ici'
            ])
            ->add('url', TextType::class, [
                'label' => 'Lien',
                'help' => 'Insérez un lien',
                'required' => false
            ])
            ->add('isParent', null, [
                'label' => 'Votre élément est-il un élément parent ?'
            ])
            ->add('type', null, [
                'label' => 'Le type de l\'élément',
                'help' => '( Page, Post ... )',
                'required' => false
            ])
            ->add('idType', null, [
                'label' => 'Identifiant de votre Type',
                'required' => false
            ])
            ->add('target', ChoiceType::class, [
                'label' => 'Cible du lien',
                'required' => false,
                'choices'  => [
                    'Normale' => null,
                    "S'ouvre dans un nouvel onglet" => '_blank',
                ],
            ])
            ->add('isList', null, [
                'label' => 'Votre élément est-il le listing des éléments du type choisi ?',
                'help' => '( Affiche en enfant la liste des Type/Entité choisie )'
            ])
            ->add('isCategoryList', null, [
                'label' => 'Votre élément est-il le listing des catégories du type choisi ?',
                'help' => '( Affiche en enfant la liste des categories du type choisi )'
            ])
            ->add('menuItemParent', null, [
                'query_builder' => function (MenuItemRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->where('m.menu = :menu')
                        ->andWhere('m.menuItemParent IS NULL')
                        ->setParameter('menu', $this->menu);
                },
                'label' => 'Element parent',
                'help' => 'Choisissez un élément parent'
            ])
//            ->add('position', null, [
//                'attr' => ['class' => 'menu-position'],
//                'label' => 'Position de l\'élément',
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
            'menu' => null
        ]);
    }
}
