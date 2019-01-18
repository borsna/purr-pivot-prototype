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
     * Mockup page for register dataset and insert it into an index
     * 
     * @Route("/register/dataset", methods={"GET"})
     */
    public function register(Request $request)
    {
        return $this->render('dataset/new.html.twig',[
            'id' => uniqid()
        ]);
    }

    /**
	 * Mockup landing page for registred datasets.
     * 
     * @Route("/dataset/{slug}", methods={"GET"})
	 */
	public function show(string $slug) {
        return $this->render('dataset/show.html.twig',[
            'dataset' => $this->getFakeMetadata($slug)
        ]);
    }

    /**
	 * Mockup endpoint for storing a registred dataset.
     * This should be done in a persistant way when done in 
     * a non-prototype mode.
     * 
     * @Route("/dataset/{slug}", methods={"PUT"})
	 */
	public function save(string $slug) {
		$content = file_get_contents('php://input');
		$json = json_decode($content);
		self::saveFakeMetadata($slug, $json);

		return $this->json((object)['status' => 'ok']);
    }

    /**
     * List unpublished datasets and give the user
     * a way to approve them via a link.
     * 
     * @Route("/admin/dataset/unpublished")
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

    /**
     * Helper function to get fake metadata from json-file
     *
     * @param string $slug
     * @return object
     */
	private function getFakeMetadata(string $slug){
		$content = file_get_contents($this->metadataDir.$slug.'.json');
		
		return json_decode($content);
	}

    /**
     * Helper function to store fake metadata
     *
     * @param string $slug
     * @param object $json
     * @return void
     */
	private function saveFakeMetadata(string $slug, $json){
        file_put_contents($this->metadataDir.$slug.'.json', json_encode($json, JSON_PRETTY_PRINT));
        $this->elasticSearchService->index($json);
	}
}