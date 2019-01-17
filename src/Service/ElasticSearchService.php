<?php
namespace App\Service;

use Elasticsearch\ClientBuilder;

class ElasticSearchService{

    private $client;

    public function __construct()
    {
        $hosts = [
            'localhost:9200',
        ];
        $this->client = ClientBuilder::create()
                            ->setHosts($hosts)
                            ->build();
    }

    public function createIndex() {

        if($this->client->indices()->exists(['index' => 'publications'])){
            $this->client->indices()->delete(['index' => 'publications']);
        }
        
        $this->client->indices()->create([
            'index' => 'publications',
            'body' => $this->mappings(),
        ]);
    }

    public function index($record){
        $params = [
            'index' => 'publications',
            'type' => 'publication',
            'id' => $record->id,
            'body' => $record
        ];
        
        return $this->client->index($params);
    }

    public function search($query){
        $params = [
            'index' => 'publications',
            'type' => 'publication',
            'body' => [
                'query' => $query,
                'highlight' => [
                    'number_of_fragments' => 3,
                    'fragment_size' => 150,
                    'fields' => [
                        'description'=> new \stdClass(),
                        'title'=> new \stdClass(),
                        'subject'=> new \stdClass()
                    ]
                ],
                'aggs' => $this->aggregations()
            ]
        ];
        
        return $this->client->search($params);
    }

    function autocomplete($field, $string){
        $params = [
            'index' => 'publications',
            'type' => 'publication',
            'body' => [
                'size' => 0,
                'aggs' => [
                    $field => [
                        'terms' => [
                            'field' => $field.'.raw',
                            'include' => '.*'.strtolower($string).'.*|.*'.ucfirst($string).'.*'
                        ]
                    ]
                ]
            ]
        ];

        return $this->client->search($params);
    }

    private function aggregations(){
        return [
            'subject' => [
                'terms' => [
                    'field' => 'subject.raw'
                ]
            ],
            'creator' => [
                'terms' => [
                    'field' => 'creator.raw'
                ]
            ],
            'date' => [
                'date_histogram' => [
                    'field' => 'date', 
                    'interval'=>'year',
                    'format' => 'yyyy',
                    'keyed' => true,
                    'order' => ['_key' => 'desc']
                ]
            ]
        ];
    }

    private function mappings(){
        return [
            'mappings' =>[
                'publication' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'english',
                        ],
                        'description' => [
                            'type' => 'text',
                            'analyzer' => 'english',
                        ],
                        'date' => [
                            'type' => 'date'
                        ],
                        'subject' => [
                            'type' => 'text',
                            'analyzer' => 'english',
                            'fields' => [
                                'raw'=>[
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'creator' => [
                            'type' => 'text',
                            'analyzer' => 'english',
                            'fields' => [
                                'raw'=>[
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'set' => [
                            'type' => 'keyword'
                        ],
                        'status' => [
                            'type' => 'keyword'
                        ]
                    ],
                ]
            ]
        ];
    }
}