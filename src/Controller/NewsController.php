<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    #[Route('/')]
    public function index(): Response {
        return new Response('<h1>Hello</h1>');
    }

    #[Route('/news/{id}')]
    public function news(int $id = null) : Response {

        $list = [
            ['title' => 'Header 1', 'body' => 'Any thing, more text and more text'],
            ['title' => 'Header 2', 'body' => '1 Any thing, more text and more text'],
            ['title' => 'Header 3', 'body' => '2 Any thing, more text and more text'],
            ['title' => 'Header 4', 'body' => '3 Any thing, more text and more text']
        ];

        return $this->render('news/news-list.html.twig', [
            'title' => 'Hello world',
            'list' => $list
        ]);
    }
}