<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilterExtension extends AbstractExtension
{
    protected $requestStack;
    protected $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('addFilter', [$this, 'addFilter']),
            new TwigFunction('removeFilter', [$this, 'removeFilter']),
        ];
    }

    public function addFilter(string $route, string $key, string $value)
    {
        $params = $this->requestStack->getCurrentRequest()->query->all();
        
        if(array_key_exists($key, $params) == FALSE || !in_array($value, $params[$key])){
            $params[$key][] = $value;
        }

        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function removeFilter(string $route, string $key, string $value)
    {
        $params = $this->requestStack->getCurrentRequest()->query->all();
        
        if(array_key_exists($key, $params) && in_array($value, $params[$key])){
            $params[$key] = array_diff($params[$key], [$value]);
        }

        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}