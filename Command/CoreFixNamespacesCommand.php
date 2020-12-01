<?php

namespace Akyos\CoreBundle\Command;

use Akyos\BuilderBundle\Entity\Component;
use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\Seo;
use Akyos\CoreBundle\Twig\CoreExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CoreFixNamespacesCommand extends Command
{
    protected static $defaultName = 'core:fix-namespaces';
    /** @var EntityManagerInterface */
    private $em;
    /** @var CoreExtension */
    private CoreExtension $coreExtension;

    public function __construct(string $name = null, EntityManagerInterface $em, CoreExtension $coreExtension)
    {
        parent::__construct($name);
        $this->em = $em;
        $this->coreExtension = $coreExtension;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fix les namespaces du core.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $seos = $this->em->getRepository(Seo::class)->findAll();

        foreach ($seos as $seo) {
            $initType = $seo->getType();

            if (!class_exists($initType)) {
                $type = $this->coreExtension->getEntityNameSpace($initType);
                $seo->setType($type);
                $this->em->flush();
            }
        }

        $io->success('Changement terminé.');

        return 0;
    }
}
