<?php

namespace App\Controller\Api;

use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
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
    public function index(Request $request, Elasticsearch $elasticsearch)
    {
        $search_text = $request->request->get('search');

        $result = $elasticsearch->search_in_catalogs($search_text);

        echo "результаты поиска для: \"$search_text\"\n";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        die();
    }
}