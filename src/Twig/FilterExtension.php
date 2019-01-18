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

    /**
     * Add filter to current request and generate path
     *
     * @param string $route The route to link to
     * @param string $key The filter key (e.g. subject, creator)
     * @param string $value The value to filter on
     * @return string relative URL including the new filter
     */
    public function addFilter(string $route, string $key, string $value) : string
    {
        $params = $this->requestStack->getCurrentRequest()->query->all();
        
        if(array_key_exists($key, $params) == FALSE || !in_array($value, $params[$key])){
            $params[$key][] = $value;
        }

        return $this->urlGenerator->generate($route, $params);
    }

    /**
     * Remove filter from current request and generate path
     *
     * @param string $route The route to link to
     * @param string $key The filter key (e.g. subject, creator)
     * @param string $value The value to remove
     * @return string relative URL including the new filter
     */
    public function removeFilter(string $route, string $key, string $value) : string
    {
        $params = $this->requestStack->getCurrentRequest()->query->all();
        
        if(array_key_exists($key, $params) && in_array($value, $params[$key])){
            $params[$key] = array_diff($params[$key], [$value]);
        }

        return $this->urlGenerator->generate($route, $params);
    }
}