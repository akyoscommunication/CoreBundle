<?php

namespace Akyos\CoreBundle\DataFixtures;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\Menu;
use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Entity\Seo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CoreInstallFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $homepage = new Page();
        $homepage
            ->setTitle('Accueil')
            ->setSlug('accueil')
            ->setPublished(true)
            ->setPosition(0)
        ;
        $manager->persist($homepage);
        $manager->flush();

        $seo = new Seo();
        $seo
            ->setNoIndex(0)
            ->setType('Page')
            ->setTypeId($homepage->getId())
        ;
        $manager->persist($seo);
        $manager->flush();

        $menuArea = new MenuArea();
        $menuArea
            ->setName('Menu principal')
            ->setSlug('menu-principal')
            ->setDescription('Menu présent sur toutes les pages, dans le header')
        ;
        $manager->persist($menuArea);
        $manager->flush();

        $menu = new Menu();
        $menu
            ->setTitle('Menu principal')
            ->setSlug('menu-principal')
        ;
        $manager->persist($menu);
        $manager->flush();

        $coreOptions = new CoreOptions();
        $coreOptions
            ->setHomepage($homepage)
            ->setSiteTitle('Nouveau site')
            ->setBackMainColor('#000000')
            ->setHasPosts(0)
            ->setHasPostDocuments(0)
            ->setHasSeoEntities(['Akyos\CoreBundle\Entity\Page'])
        ;
        $manager->persist($coreOptions);
        $manager->flush();
    }
}
