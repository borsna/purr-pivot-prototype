<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ElasticSearchService;


class HomeController extends AbstractController
{
    private $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }
    /**
      * @Route("/")
      */
    public function index(Request $request)
    {
        $filters = [
            'subject' => $request->query->get('subject'),
            'creator' => $request->query->get('creator'),
        ];

        if($request->query->get('q')){
            $query = [
                'bool'=>[
                    'should' => [
                        'simple_query_string' => [
                            'query' => $request->query->get('q').' AND status:published',
                            'fields' => [
                                'title^5', 
                                'description', 
                                'subject^2', 
                                'creator'
                            ],
                        ]
                    ],
                    'minimum_should_match' => 1,
                ]
            ];
        }else{
            $query = [
                'bool'=>[
                    'should' => [
                        'match_all'=>new \stdClass()
                    ],
                    'minimum_should_match' => 1,
                ]
            ];
        }

        $query['bool']['filter'][] = ['term' => ['status' => 'published']];

        $query['bool']['filter'] = array_merge($query['bool']['filter'], $this->_getFilterQueries($filters));

        $result = $this->elasticSearchService->search($query);

        return $this->render('home/index.html.twig', [
            'result' => $result,
            'debug' => $query,
            'q' => $request->query->get('q', ''),
            'filters' => $filters
        ]);
    }

    private function _getFilterQueries($filters){
        $result = [];
        foreach($filters as $key => $values){
            if(is_array($values)){
                foreach($values as $value){
                    $result[] = ['term' => [$key.'.raw' => $value]];
                }
            }
        }

        return $result;
    }
}