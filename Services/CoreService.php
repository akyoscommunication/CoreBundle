<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Entity\CoreOptions;
use Doctrine\ORM\EntityManagerInterface;

class CoreService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function checkIfSingleEnable($entity): bool
    {
        $coreOptions = $this->em->getRepository(CoreOptions::class)->findAll();
        if ($coreOptions) {
            if (preg_grep('/'.$entity.'$/i', $coreOptions[0]->getHasSingleEntities())) {
                return true;
            } else return false;
        } else return false;
    }

    public function checkIfArchiveEnable($entity): bool
    {
        $coreOptions = $this->em->getRepository(CoreOptions::class)->findAll();
        if ($coreOptions) {
            if (preg_grep('/'.$entity.'$/i', $coreOptions[0]->getHasArchiveEntities())) {
                return true;
            } else return false;
        } else return false;
    }

    public function checkIfSeoEnable($entity): bool
    {
        $coreOptions = $this->em->getRepository(CoreOptions::class)->findAll();
        if ($coreOptions) {
            if (preg_grep('/'.$entity.'$/i', $coreOptions[0]->getHasSeoEntities())) {
                return true;
            } else return false;
        } else return false;
    }

    public function checkIfBundleEnable($bundle, $options, $entity)
    {
        if (class_exists($bundle)) {
            $opt = $this->em->getRepository($options)->findAll();
            if ($opt) {
                if (preg_grep('/'.$entity.'$/i', $opt[0]->getHasBuilderEntities())) {
                    return true;
                } else return false;
            } else return false;
        } else return false;
    }

    public function getEntityAndFullString(string $entitySlug) {
        $entityFullName = null;
        $entity = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                try {
                    $constant_reflex = new \ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
                    $constant_value = $constant_reflex->getValue();
                } catch (\ReflectionException $e) {
                    $constant_value = null;
                }
                if(null !== $constant_value) {
                    if($m->getName()::ENTITY_SLUG === $entitySlug) {
                        $entityFullName = $m->getName();
                        $entity = array_reverse(explode('\\', $entityFullName))[0];
                    }
                }
            }
        }

        return [
            $entityFullName,
            $entity
        ];
    }
}