<?php
namespace App\Service;

class OaiPmhService{

    public function harvest($url, $set = 'publications:dataset') : array{
        $client = new \Phpoaipmh\Client($url);
        $endpoint = new \Phpoaipmh\Endpoint($client);
        
        $result = array();

        foreach ($endpoint->listRecords('oai_dc', null, null, $set) as $record) {
            
            $record->registerXPathNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
            $record->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
            $record->registerXPathNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
        
            $resource = (object)[
                'title' => (string)$record->xpath('.//dc:title')[0],
                'id' => '',
                'set' => $set,
                'identifier' => (string)$record->xpath('.//dc:identifier')[0],
                'date' => (string)$record->xpath('.//dc:date')[0],
                'description' => '',
                'subject' => [],
                'creator' => [],
                'status' => 'published'
            ];

            if($record->xpath('.//dc:description') != null){
                $resource->description = (string)$record->xpath('.//dc:description')[0];
            }
            $resource->id = str_replace('http://dx.doi.org/', '', $resource->identifier);
            $resource->id = str_replace('/', '-', $resource->id);

            if (strpos($resource->title, ' [version ') !== false){
                $titleParts = explode(' [version ', $resource->title);
                $resource->title = $titleParts[0];
                $resource->version = str_replace(']', '', $titleParts[1]);
            }

            foreach($record->xpath('.//dc:subject') as $subject){
                $resource->subject[] = (string)$subject;
            }

            foreach($record->xpath('.//dc:creator') as $creator){
                $resource->creator[] = (string)$creator;
            }

            $result[] = $resource;
        }
        return $result;
    }
}