<?php

namespace App\Service;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Response\Elasticsearch as Elasticsearch_Response;
use Http\Promise\Promise;
use RuntimeException;
use stdClass;

class Elasticsearch
{

    private Client $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct(
        string $elasticsearch_connection_type,
        string $elasticsearch_host,
        string $elasticsearch_user,
        string $elasticsearch_password,
        string $elasticsearch_cloud_id,
        string $elasticsearch_api_key
    ) {
        $Elasticsearch_client = ClientBuilder::create();

        switch ($elasticsearch_connection_type){
            case 'PASSWORD':
                $Elasticsearch_client
                    ->setHosts([$elasticsearch_host])
                    ->setBasicAuthentication($elasticsearch_user, $elasticsearch_password);
                break;
            case 'API_KEY':
                $Elasticsearch_client
                    ->setElasticCloudId($elasticsearch_cloud_id)
                    ->setApiKey($elasticsearch_api_key);
                break;
            default:
                throw new RuntimeException('Elasticsearch connection type don`t specified', 500);
        }

        $this->client = $Elasticsearch_client->build();
    }

    /**
     * @param string $text
     * @return array
     *
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function search_in_catalogs(string $text): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $text,
                        'fields' => ['title^2', 'text'],
                        'operator' => 'OR',
                        'fuzziness' => 'AUTO',
                        'fuzzy_transpositions' => true,
                        'analyzer' => 'catalog_content'
                    ]
                ],
                'fields' => ['title'],
                '_source' => false,
                'highlight' => [
                    'fields' => [
                        'text' => new stdClass()
                    ]
                ]
            ]
        ])->asArray();
    }

    /**
     * @param string $text
     * @return array
     *
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    public function suggests_in_catalogs( string $text ): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => [
                'suggest' => [
                    'text' => $text,
                    'simple_phrase' => [
                        'completion' => [
                            'field' => 'text'
                        ],
                        'phrase' => [
                            'field' => 'text',
                            'size' => 2,
                            'gram_size' => 1,
                            'max_errors' => 5,
                            'direct_generator' => [[
                                'field' => 'text',
                                'suggest_mode' => 'always'
                            ]],
                            "smoothing" => [
                                "laplace" => [
                                    "alpha" => 0.7
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ])->asArray();
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function uploadDocument(int $id, string $title, string $text, int $size): Elasticsearch_Response|Promise
    {
        return $this->client->create([
            'id' => $id,
            'index' => 'catalogs',
            'body' => [
                'title' => $title,
                'text' => $text,
                'size' => $size,
            ]
        ]);
    }

}