<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\Option;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionType extends AbstractType
{
    protected $optionId;
    protected $pages;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->optionId = $options['option'];
        $this->pages = $options['pages'];

        switch ($builder->getData()->getType()) {

            case 'textarea':
                $builder
                    ->add('value', CKEditorType::class, array(
                        'required'    => false,
                        'config'      => array(
                            'placeholder'    => "Texte",
                            'height'         => 50,
                            'entities'       => false,
                            'basicEntities'  => false,
                            'entities_greek' => false,
                            'entities_latin' => false,
                        ),
                        'label'    => false
                    ))
                ;
                break;

            case 'tel':
                $builder
                    ->add('value', TelType::class, array(
                        'attr'              => array(
                            'placeholder'       => "Numéro",
                        ),
                        'label'                 => false,
                        'required'              => false
                    ))
                ;
                break;

            case 'mail':
                $builder
                    ->add('value', EmailType::class, array(
                        'attr'              => array(
                            'placeholder'       => "Email",
                        ),
                        'label'                 => false,
                        'required'              => false
                    ))
                ;
                break;

            case 'pagelink':
                $builder
                    ->add('value', ChoiceType::class, array(
                        'choices' => $this->pages,
                        'label'  => false
                    ))
                ;
                break;

            case 'link':
                $builder
                    ->add('value', UrlType::class, array(
                        'attr'              => array(
                            'placeholder'       => "Lien",
                        ),
                        'label'                 => false,
                        'required'              => false
                    ))
                ;
                break;

            case 'image':
                $builder->add('value',FileManagerType::class);
                break;

            default:
                $builder
                    ->add('value', TextType::class, array(
                        'attr'              => array(
                            'placeholder'       => "Valeur",
                        ),
                        'label'                 => false,
                        'required'              => false
                    ))
                ;
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Option::class,
            'option' => null,
            'pages' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ac_back_option_form'.$this->optionId;
    }
}
