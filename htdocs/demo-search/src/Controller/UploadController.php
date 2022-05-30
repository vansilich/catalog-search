<?php

namespace App\Controller;

use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{

    private string $catalogs_dir = '.test-catalogs-json';

    public function __construct(
        private readonly Elasticsearch $elasticsearch
    ){}

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    #[Route('/upload/all', name: 'upload_all', methods: ['GET'])]
    public function uploadAll(string $projectDir): Response
    {
        $catalogsPATH = sprintf('%s/%s', $projectDir, $this->catalogs_dir);
        foreach (glob($catalogsPATH.'/*') as $key => $file){
            $text = file_get_contents($file);
            $name = trim(basename($file), '.tx');

            $this->elasticsearch->uploadDocument($key, $name, $text, 228);
        }

        return new Response(null, 201);
    }

}