<?php

namespace Akyos\CoreBundle\DoctrineListener;

use Akyos\CoreBundle\Annotations\SlugRedirect;
use Akyos\CoreBundle\Entity\Redirect301;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;

class SlugRedirectListener
{
    // On post update, if entity has "slug" property and slug has changed, create a new Redirect301 object with old slug and objectId.
    // This way if someone search for /oldSlug page, we can redirect him and prevent 404 error.
    // Find is there already is a Redirect301 object with with same oldSlug and if so change the new slug.
    // This way we'll never have duplicate oldSlug on same entity over the time.

    public function postUpdate(LifecycleEventArgs $args)
    {
        // Update object
        $entity = $args->getEntity();
        // Update reflexionObject (= class with all metadatas)
        $reflectionObject = new \ReflectionObject($args->getObject());
        // Deprecated but currently needed...
        AnnotationRegistry::registerUniqueLoader('class_exists');
        /** @var AnnotationReader $reader */
        $reader = new AnnotationReader;

        if($reflectionObject->hasProperty('slug')) {

            /** @var \ReflectionProperty $reflectionProperty */
            $reflectionProperty = $reflectionObject->getProperty('slug');
            /** @var Annotation $annotation */
            $annotation = $reader->getPropertyAnnotation($reflectionProperty, SlugRedirect::class);

            if(null !== $annotation) {

                /** @var EntityManager $em */
                $em = $args->getEntityManager();
                /** @var UnitOfWork $uow */
                $uow = $em->getUnitOfWork();
                /** @var array $changeSet */
                $changeSet = $uow->getEntityChangeSet($entity);

                if(array_key_exists('slug', $changeSet)) {

                    /** @var Redirect301 $sameNewSlugRedirect */
                    $sameNewSlugRedirect = $em->getRepository(Redirect301::class)->findOneBy(['newSlug' => $changeSet['slug'][0], 'objectType' => $reflectionObject->getName()]);
                    /** @var Redirect301 $sameOldSlugRedirect */
                    $sameOldSlugRedirect = $em->getRepository(Redirect301::class)->findOneBy(['oldSlug' => $changeSet['slug'][1], 'objectType' => $reflectionObject->getName()]);

                    // 1 - No element has the new slug as oldSlug, new slug has not been changed in 3 => need to create a new Redirect301.
                    if(!$sameNewSlugRedirect && !$sameOldSlugRedirect) {
                        /** @var Redirect301 $redirect */
                        $redirect = new Redirect301();
                        $redirect->setObjectId($entity->getId());
                        $redirect->setObjectType($reflectionObject->getName());
                        $redirect->setOldSlug($changeSet['slug'][0]);
                        $redirect->setNewSlug('('.$changeSet['slug'][1].')');
                        $em->persist($redirect);
                        $em->flush();
                    }

                    // 2 - New entity slug has been changed in 3, postUpdate event fires a second (or more) time, there is a Redirect301 for same entity where newSlug = new entity slug: don't have to create a second Redirect301 but need to change his newSlug.
                    if($sameNewSlugRedirect && $sameNewSlugRedirect->getObjectId() === $entity->getId()) {
                        // 2.1 - If there is another Redirect301 for different element with same oldSlug as entity newSlug, then the entity slug will be changed again and the postUpdate event will fire again.
                        // We change the Redirect301 newSlug with new entity slug value, so it can match the previous if condition (2) on next postUpdate event.
                        $sameNewSlugRedirect->setNewSlug($changeSet['slug'][1]);
                        // 2.2 - But if there is no another Redirect301 for different element with same oldSlug as entity newSlug, then the entity slug won't be changed again, it's the last "postUpdate loop" iteration.
                        // We have to change Redirect301 newSlug, but next time we don't want it to match the if condition (2), so we add '()'
                        if(!$sameOldSlugRedirect) {
                            $sameNewSlugRedirect->setNewSlug('('.$changeSet['slug'][1].')');
                        } elseif ($sameOldSlugRedirect->getObjectId() === $entity->getId()) {
                            $sameNewSlugRedirect->setNewSlug('('.$changeSet['slug'][1].')');
                        }
                        $em->flush();
                    }

                    // 3 - New slug is the same as another element oldSlug: if we store it, the next time this one will change, we'll have two Redirect301 with same oldSlug, and we won't be able to know which one to choose.
                    // We have to change the new entity slug, so this will fire the postUpdate event again, and create another Redirect301... but we only need one Redirect301 with first oldSlug and last newSlug to avoid collisions.
                    // Maybe new slug will match a Redirect301 oldSlug again, then we'll have to change new entity slug again, and repeat it until there is no Redirect301 with same oldSlug. This will create a "postUpdate loop" so we have to handle it.
                    if($sameOldSlugRedirect && $sameOldSlugRedirect->getObjectId() !== $entity->getId()) {

                        // 3.1 - If it's the first "postUpdate loop" iteration then there is no Redirect301 to update, but no one has been created in 1, so we have to create it here. Then, in the next iterations, his newSlug will be change in 2.
                        if(!($sameNewSlugRedirect))  {
                            /** @var Redirect301 $redirect */
                            $redirect = new Redirect301();
                            $redirect->setObjectId($entity->getId());
                            $redirect->setObjectType($reflectionObject->getName());
                            $redirect->setOldSlug($changeSet['slug'][0]);
                            $redirect->setNewSlug($changeSet['slug'][1]);
                            $em->persist($redirect);
                            $em->flush();
                        }

                        $entity->setSlug($changeSet['slug'][1].'-1');
                        $em->flush();
                    }
                }
            }
        }

        return true;
    }
}