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
use Symfony\Component\Form\CallbackTransformer;
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
	private $em;
	private $customFieldValueRepository;
	
	public function __construct(PageRepository $pageRepository, PostRepository $postRepository, EntityManagerInterface $em, CustomFieldValueRepository $customFieldValueRepository)
	{
		$pages = $pageRepository->findAll();
		foreach ($pages as $page) {
			$this->pages[$page->getTitle()] = $page->getId();
		}
		$posts = $postRepository->findAll();
		foreach ($posts as $post) {
			$this->posts[$post->getTitle()] = $post->getId();
		}
		$this->em = $em;
		$this->customFieldValueRepository = $customFieldValueRepository;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $customFieldValue = $builder->getData();
        if ($customFieldValue) {
            $field = $customFieldValue->getCustomField();
            switch ($field->getType()) {
                case 'textarea_html':
                    $builder
                        ->add('value', CKEditorType::class, [
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'config' => [
                                'placeholder' => "Texte",
                                'height' => 300,
                                'entities' => false,
                                'basicEntities' => false,
                                'entities_greek' => false,
                                'entities_latin' => false,
                            ],
                            'label' => $field->getTitle(),
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'textarea':
                    $builder
                        ->add('value', TextareaType::class, [
                            'label' => $field->getTitle(),
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'tel':
                    $builder
                        ->add('value', TelType::class, [
                            'attr' => [
                                'placeholder' => "Numéro",
                            ],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'mail':
                    $builder
                        ->add('value', EmailType::class, [
                            'attr' => [
                                'placeholder' => "Email",
                            ],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'pagelink':
                    $builder
                        ->add('value', ChoiceType::class, [
                            'choices' => $this->pages,
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'label' => $field->getTitle(),
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'postlink':
                    $builder
                        ->add('value', ChoiceType::class, [
                            'choices' => $this->posts,
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'label' => $field->getTitle(),
                            'help' => $field->getDescription(),
                        ]);
                    break;
                case 'entity':
                    $choices = [];
                    $elements = $this->em->getRepository($field->getEntity())->findAll();
                    foreach ($elements as $element) {
                        $choices[(string)$element] = $element->getId();
                    }
                    $builder
                        ->add('value', ChoiceType::class, [
                            'choices' => $choices,
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'label' => $field->getTitle(),
                            'placeholder' => "Sélectionnez un élément",
                            'help' => $field->getDescription(),
                            'by_reference' => false,
                        ]);
                    break;

                case 'entity_multiple':
                    $choices = [];
                    $elements = $this->em->getRepository($field->getEntity())->findAll();
                    foreach ($elements as $element) {
                        $choices[(string)$element] = $element->getId();
                    }
                    $builder
                        ->add('value', ChoiceType::class, [
                            'choices' => $choices,
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'label' => $field->getTitle(),
                            'placeholder' => "Sélectionnez un élément",
                            'help' => $field->getDescription(),
                            'by_reference' => false,
                            'multiple' => true,
                            'attr' => [
                                'class' => 'js-select2',
                            ],
                        ])
                    ;

                    $builder->get('value')
                        ->addModelTransformer(new CallbackTransformer(
                            function ($d): array {
                                return explode('SEPARATOR', $d);
                            },
                            function ($d): string {
                                return implode('SEPARATOR', $d);
                            }
                        ))
                    ;
                    break;

                case 'link':
                    $builder
                        ->add('value', UrlType::class, [
                            'attr' => [
                                'placeholder' => "Lien",
                            ],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'image':
                    $builder->add('value', FileManagerType::class, [
                        'label' => $field->getTitle(),
                        'config' => 'full',
                        'help' => $field->getDescription(),
                    ]);
                    break;

                case 'gallery':
                    if (!is_array($customFieldValue->getValue())) {
                        $customFieldValue->setValue([$customFieldValue->getValue()]);
                    }
                    $builder->add('value', FileManagerCollectionType::class, [
                        'label' => $field->getTitle(),
                        'help' => $field->getDescription(),
                        'entry_options' => [
                            'config' => 'full'
                        ]
                    ]);
                    break;

                case 'int':
                    $builder
                        ->add('value', IntegerType::class, [
                            'attr' => [
                                'placeholder' => "Valeur",
                            ],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'select':
                    $choices = [];
                    $values = explode('|', $field->getOptions());
                    $fieldOptions = array_slice($values, 1);
                    foreach ($fieldOptions as $option) {
                        $vs = explode(';', $option);
                        if (count($vs) < 2) {
                            $choices[$vs[0]] = $vs[0];
                        } else {
                            if ($vs[1] === '') {
                                $choices[$vs[0]] = $vs[0];
                            } else {
                                $choices[$vs[0]] = $vs[1];
                            }
                        }
                    }

                    $builder
                        ->add('value', ChoiceType::class, [
                            'placeholder' => $values[0],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'choices' => $choices,
                            'help' => $field->getDescription(),
                        ]);
                    break;
                case 'bool':

                    $builder
                        ->add('value', CheckboxType::class, [
                            'attr' => [
                                'placeholder' => "Valeur",
                            ],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'data' => (bool)$customFieldValue->getValue(),
                            'help' => $field->getDescription(),
                        ]);
                    break;

                case 'color':
                    $builder
                        ->add('value', ColorType::class, [
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'help' => $field->getDescription(),
                        ]);
                    break;

                default:
                    $builder
                        ->add('value', TextType::class, [
                            'attr' => [
                                'placeholder' => "Valeur",
                            ],
                            'label' => $field->getTitle(),
                            'required' => $field->getIsRequired() !== null ? $field->getIsRequired() : false,
                            'help' => $field->getDescription(),
                        ]);
                    break;
            }
        }
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => CustomFieldValue::class,
		]);
	}
}
