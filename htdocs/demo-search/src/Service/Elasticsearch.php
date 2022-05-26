<?php

namespace App\Service;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;

class Elasticsearch
{

    private Client $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct(
        string $elasticsearch_host,
        string $elasticsearch_user,
        string $elasticsearch_password,
    ) {
        $this->client = ClientBuilder::create()
            ->setHosts([$elasticsearch_host])
            ->setBasicAuthentication($elasticsearch_user, $elasticsearch_password)
            ->build();
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
                    'bool' => [
                        'must' => [],
                        'should' => [
                            [
                                'wildcard' => [
                                    'text' => [
                                        'value' => '*' . $text . '*',
                                        'boost' => 1.0,
                                        'rewrite' => 'constant_score'
                                    ]
                                ]
                            ],
                            [
                                'multi_match' => [
                                    'query' => $text,
                                    'fields' => ['title^2', 'text'],
                                    'operator' => 'OR',
                                    'fuzziness' => '0',
                                    'fuzzy_transpositions' => true,
                                    'analyzer' => 'catalog_content'
                                ]
                            ]
                        ]
                    ]
                ],
                'fields' => ['title'],
                '_source' => false,
                'highlight' => [
                    'fields' => [
                        'text' => new \stdClass()
                    ]
                ],
                'suggest' => [
                    'text' => $text,
                    'simple_phrase' => [
                        'phrase' => [
                            'field' => 'text',
                            'size' => 2,
                            'gram_size' => 1,

                            "highlight" => [
                                "pre_tag" => '<b>',
                                "post_tag" => '</b>',
                            ]
                        ]
                    ]
                ]
            ]
        ])->asArray();
    }

}