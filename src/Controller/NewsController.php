<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController
{
    #[Route('/')]
    public function index(): Response {
        return new Response('<h1>Hello</h1>');
    }

    #[Route('/news/{id}')]
    public function news(int $id = null) : Response {
        return new Response("<h1>News: {$id}</h1>");

    }
}