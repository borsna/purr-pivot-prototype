<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\ElasticSearchService;


class AutocompleteController extends AbstractController
{
    private $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }
    
    /**
      * @Route("/api/autocomplete/{field}/{string}")
      */
    public function autocomplete(Request $request, $field, $string)
    {
        $result = $this->elasticSearchService->autocomplete($field, $string);

        $list = [];
        foreach($result['aggregations'][$field]['buckets'] as $bucket){
            $list[] = $bucket['key'];
        }

        return new JsonResponse($list);

    }
}