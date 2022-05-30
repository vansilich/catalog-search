<?php

namespace App\Controller\Api;

use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class SearchController extends AbstractController
{

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    #[Route('/catalogs/search', name: 'catalogs_search')]
    public function search(Request $request, Elasticsearch $elasticsearch)
    {
        $search_text = $request->request->get('search');

        $result = $elasticsearch->search_in_catalogs($search_text);

        echo "результаты поиска для: \"$search_text\"\n";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        die();
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    #[Route('/catalogs/suggests', name: 'catalogs_search_suggests')]
    public function suggests(Request $request, Elasticsearch $elasticsearch): JsonResponse
    {
        $json = $request->toArray();
        if ( !isset($json['search']) ) {
            return $this->json([ 'suggests' => [] ]);
        }

        $text = $json['search'];
        $search_suggests = $elasticsearch->suggests_in_catalogs($text)['suggest']['simple_phrase'][0]['options'];

        $search_suggests_response = [];
        if ( !empty($search_suggests) ){
            foreach ($search_suggests as $suggest_item){
                $search_suggests_response[] = $suggest_item['text'];
            }
        }

        return $this->json([ 'suggests' => $search_suggests_response ]);
    }
}