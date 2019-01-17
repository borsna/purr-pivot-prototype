<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ElasticSearchService;


class DatasetController extends AbstractController
{
    private $elasticSearchService;
    private $metadataDir;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
        $this->metadataDir = __DIR__.'/../../public/metadata/dataset/';
    }
    
    /**
      * @Route("/dataset/register")
      */
    public function register(Request $request)
    {
        return $this->render('dataset/new.html.twig',[
            'id' => uniqid()
        ]);
    }

    /**
	 * @Route("/dataset/{slug}", name="dataset_save", methods={"PUT"})
	 */
	public function save($slug) {
		$content = file_get_contents('php://input');
		$json = json_decode($content);
		self::saveFakeMetadata($slug, $json);

		return $this->json((object)['status' => 'ok']);
    }

    /**
     * @Route("/dataset/unpublished")
     */
    public function listUnpublished(){

        $result = $this->elasticSearchService->search([
            'bool'=>[
                'should' => [
                    'match_all'=>new \stdClass()
                ],
                'minimum_should_match' => 1,
                'filter' => [
                    [
                        'term' => ['status' => 'submitted']
                    ]
                ]
            ]
        ]);

        $datasets = [];
        foreach($result['hits']['hits'] as $dataset){
            $datasets[] = $dataset['_source'];
        }

        return $this->render('dataset/unpublished.html.twig',[
            'datasets' => $datasets
        ]);
    }
    
	private function getFakeMetadata($slug){
		$content = file_get_contents($this->metadataDir.$slug.'.json');
		
		return json_decode($content);
	}

	private function saveFakeMetadata($slug, $json){
        file_put_contents($this->metadataDir.$slug.'.json', json_encode($json, JSON_PRETTY_PRINT));
        $this->elasticSearchService->index($json);
	}
}