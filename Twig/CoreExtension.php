<?php

namespace Akyos\CoreBundle\Twig;

use Akyos\CoreBundle\Service\CoreMailer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ReflectionException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CoreExtension extends AbstractExtension
{
    private EntityManagerInterface $em;

    private CoreMailer $mailer;

    private ParameterBagInterface $parameterBag;

    public function __construct(EntityManagerInterface $entityManager, CoreMailer $mailer, ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [// If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('dynamicVariable', [$this, 'dynamicVariable']), new TwigFilter('truncate', [$this, 'truncate']), new TwigFilter('lcfirst', [$this, 'lcfirst']),

        ];
    }

    /**
     * @param $value
     * @param int $length
     * @param string $after
     * @return string
     */
    public function truncate($value, int $length, string $after)
    {
        if (strlen($value) > $length) {
            return mb_substr($value, 0, $length, 'UTF-8') . $after;
        }
        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function lcfirst($value): string
    {
        return lcfirst($value);
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('useClosure', [$this, 'useClosure']), new TwigFunction('dynamicVariable', [$this, 'dynamicVariable']), new TwigFunction('matchSameEntity', [$this, 'matchSameEntity']), new TwigFunction('instanceOf', [$this, 'isInstanceOf']), new TwigFunction('sendExceptionMail', [$this, 'sendExceptionMail']), new TwigFunction('get_class', 'get_class'), new TwigFunction('class_exists', 'class_exists'), new TwigFunction('countElements', [$this, 'countElements']), new TwigFunction('getParameter', [$this, 'getParameter']),];
    }

    /**
     * @param $str
     * @param $entity
     * @return bool
     */
    public function matchSameEntity($str, $entity): bool
    {
        if (!is_object($entity)) {
            return false;
        }
        return $str === get_class($entity);
    }

    /**
     * @param $object
     * @param null $class
     * @return bool|string
     * @throws ReflectionException
     */
    public function isInstanceOf($object, $class = null)
    {
        if (!$class) {
            return gettype($object);
        }
        if (!is_object($object)) {
            return false;
        }
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->isInstance($object);
    }

    /**
     * @param $exceptionMessage
     * @return bool|Exception
     */
    public function sendExceptionMail($exceptionMessage)
    {
        try {
            $this->mailer->sendMail(["thomas.sebert.akyos@gmail.com"], 'Nouvelle erreur sur le site ' . $_SERVER['SERVER_NAME'], $exceptionMessage, 'Nouvelle erreur sur le site ' . $_SERVER['SERVER_NAME'], null, null, ["lilian.akyos@gmail.com", "johan@akyos.com"], null, null, null, null, 'SMTP');
            return true;
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * @param string $entity
     * @return int
     */
    public function countElements(string $entity): int
    {
        return $this->em->getRepository($entity)->count([]);
    }

    /**
     * @param string $parameterName
     * @return array|bool|float|int|string|null
     */
    public function getParameter(string $parameterName)
    {
        return $this->parameterBag->get($parameterName);
    }

    /**
     * @param \Closure $closure
     * @param $params
     * @return mixed
     */
    public function useClosure(\Closure $closure, $params)
    {
        return $closure($params);
    }

    /**
     * @param $el
     * @param $field
     * @return mixed
     */
    public function dynamicVariable($el, $field)
    {
        $getter = 'get' . $field;
        if (count(explode(';', $field)) > 1) {
            $getter1 = 'get' . explode(';', $field)[0];
            $getter2 = 'get' . explode(';', $field)[1];
            $value = $el->$getter1() ? $el->$getter1()->$getter2() : '';
        } else {
            $value = $el->$getter();
        }
        if (is_array($value)) {
            $arrayValue = "";
            foreach ($value as $key => $item) {
                $arrayValue .= $item;
                if ($key !== (count($value) - 1)) {
                    $arrayValue .= ", ";
                }
            }
            return $arrayValue;
        }
        return $value;
    }
}
