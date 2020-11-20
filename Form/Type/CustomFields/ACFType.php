<?php

namespace Akyos\CoreBundle\Form\Type\CustomFields;

use Akyos\CoreBundle\Entity\CustomField;
use Akyos\CoreBundle\Entity\CustomFieldsGroup;
use Akyos\CoreBundle\Entity\CustomFieldValue;
use Akyos\CoreBundle\Repository\CustomFieldsGroupRepository;
use Akyos\CoreBundle\Repository\CustomFieldValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ACFType extends AbstractType
{
    private $customFieldValueRepository;
    private $customFieldsGroupRepository;
    private $entityManager;

    public function __construct(CustomFieldValueRepository $customFieldValueRepository, CustomFieldsGroupRepository $customFieldsGroupRepository, EntityManagerInterface $entityManager)
    {
        $this->customFieldValueRepository = $customFieldValueRepository;
        $this->customFieldsGroupRepository = $customFieldsGroupRepository;
        $this->entityManager = $entityManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Réglages complémentaires',
            'entity' => '',
            'object_id' => '',
        ]);

        $resolver->setAllowedTypes('entity', 'string');
        $resolver->setAllowedTypes('object_id', 'integer');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customFieldsGroups = $this->customFieldsGroupRepository->findBy(['entity' => $options['entity']]);
        foreach($customFieldsGroups as $customFieldsGroup) {
            /** @var CustomFieldsGroup $customFieldsGroup */
            foreach($customFieldsGroup->getCustomFields() as $customField) {
                /** @var CustomField $customField */
                $customFieldValue = $this->customFieldValueRepository->findOneBy(['customField' => $customField, 'objectId' => $options['object_id']]);
                if(!$customFieldValue) {
                    $customFieldValue = new CustomFieldValue();
                    $customFieldValue->setCustomField($customField);
                    $customFieldValue->setObjectId($options['object_id']);
                    $this->entityManager->persist($customFieldValue);
                }
                $builder
                    ->add('customField'.$customField->getId(), CustomFieldValueType::class, [
                        'label' => false,
                        'data' => $customFieldValue,
                    ])
                ;
            }
        }
    }

    public function getBlockPrefix()
    {
        return 'akyos_custom_field';
    }
}
