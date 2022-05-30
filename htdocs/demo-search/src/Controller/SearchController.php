<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(): Response
    {
        return new Response('
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
                <title>Тест поиска</title>
                
                <link rel="stylesheet" href="/test.css">
                <script src="/test.js" defer></script>
            </head>
            <body>
              
              <div class="container">
                  <div class="search-container">
                      <form action="/api/catalogs/search" method="POST">
                            <input class="main-input" name="search" type="text" autocomplete="off">
                            <button type="submit">поиск!</button>
                        </form>
                        
                        <div class="suggestions"></div>
                    </div>
                </div>
        
            </body>
            </html>
        ');
    }
}
