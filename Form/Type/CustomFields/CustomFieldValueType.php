<?php

namespace Akyos\CoreBundle\Form\Type\CustomFields;

use Akyos\CoreBundle\Entity\CustomFieldValue;
use Akyos\CoreBundle\Repository\CustomFieldValueRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\PostRepository;
use Akyos\FileManagerBundle\Form\Type\FileManagerCollectionType;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldValueType extends AbstractType
{
    private $pages;
    private $posts;
    private EntityManagerInterface $em;
    private CustomFieldValueRepository $customFieldValueRepository;

    public function __construct(PageRepository $pageRepository, PostRepository $postRepository, EntityManagerInterface $em, CustomFieldValueRepository $customFieldValueRepository) {
        $pages = $pageRepository->findAll();
        foreach($pages as $page) {
            $this->pages[$page->getTitle()] = $page->getId();
        }
        $posts = $postRepository->findAll();
        foreach($posts as $post) {
            $this->posts[$post->getTitle()] = $post->getId();
        }
        $this->em = $em;
        $this->customFieldValueRepository = $customFieldValueRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $formModifier = function (FormInterface $form, CustomFieldValue $customFieldValue = null) {
            if ($customFieldValue != null) {
                $field = $customFieldValue->getCustomField();
                switch ($field->getType()) {
                    case 'textarea_html':
                        $form
                            ->add('value', CKEditorType::class, [
                                'required'    => false,
                                'config'      => [
                                    'placeholder'    => "Texte",
                                    'height'         => 300,
                                    'entities'       => false,
                                    'basicEntities'  => false,
                                    'entities_greek' => false,
                                    'entities_latin' => false,
                                ],
                                'label'    => $field->getTitle(),
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'textarea':
                        $form
                            ->add('value', TextareaType::class, [
                                'label'    => $field->getTitle(),
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'tel':
                        $form
                            ->add('value', TelType::class, [
                                'attr'              => [
                                    'placeholder'       => "Numéro",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'mail':
                        $form
                            ->add('value', EmailType::class, [
                                'attr'              => [
                                    'placeholder'       => "Email",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'pagelink':
                        $form
                            ->add('value', ChoiceType::class, [
                                'choices' => $this->pages,
                                'required' => false,
                                'label'  => $field->getTitle(),
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'postlink':
                        $form
                            ->add('value', ChoiceType::class, [
                                'choices' => $this->posts,
                                'required' => false,
                                'label' => $field->getTitle(),
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;
                    case 'entity':
                        $choices = [];
                        $elements = $this->em->getRepository($field->getEntity())->findAll();
                        foreach($elements as $element) {
                            $choices[(string)$element] = $element->getId();
                        }
                        $form
                            ->add('value', ChoiceType::class, [
                                'choices'=> $choices,
                                'required' => false,
                                'label'=> $field->getTitle(),
                                'placeholder'=> "Sélectionnez un élément",
                                'help' => $field->getDescription(),
                                'by_reference' => false,
                            ])
                        ;
                        break;

                    case 'link':
                        $form
                            ->add('value', UrlType::class, [
                                'attr'              => [
                                    'placeholder'       => "Lien",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'image':
                        $form->add('value',FileManagerType::class, [
                            'label' => $field->getTitle(),
                            'config' => 'full',
                            'help' => $field->getDescription(),
                        ]);
                        break;

                    case 'gallery':
                        if (!is_array($customFieldValue->getValue())) {
                            $customFieldValue->setValue([$customFieldValue->getValue()]);
                        }
                        $form->add('value',FileManagerCollectionType::class, [
                            'label' => $field->getTitle(),
                            'help' => $field->getDescription(),
                            'entry_options' => [
                                'config' => 'full'
                            ]
                        ]);
                        break;

                    case 'int':
                        $form
                            ->add('value', IntegerType::class, [
                                'attr'              => [
                                    'placeholder'       => "Valeur",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'select':
                        $values = [];
                        foreach($field->getFieldValues() as $fieldValue) {
                            $exploded = explode(':', $fieldValue);
                            $values[$exploded[0]] = $exploded[1];
                        }

                        $form
                            ->add('value', ChoiceType::class, [
                                'attr'              => [
                                    'placeholder'       => "Valeur",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'choices'               => $values,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;
                    case 'bool':

                        $customFieldValue->setValue((bool)$customFieldValue->getValue());
                        $form
                            ->add('value' , CheckboxType::class, [
                                'attr' => [
                                    'placeholder'       => "Valeur",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    case 'color':
                        $form
                            ->add('value', ColorType::class, [
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;

                    default:
                        $form
                            ->add('value', TextType::class, [
                                'attr'              => [
                                    'placeholder'       => "Valeur",
                                ],
                                'label'                 => $field->getTitle(),
                                'required'              => false,
                                'help' => $field->getDescription(),
                            ])
                        ;
                        break;
                }
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();
                $formModifier($event->getForm(), $data);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomFieldValue::class,
        ]);
    }
}
