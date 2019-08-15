<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Urls;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {

        dump($this->getDoctrine()->getRepository(User::class)->findAll());
        dump($this->getDoctrine()->getRepository(Urls::class)->findLastUrls(10));
        return $this->render('home/index.html.twig');
    }
}
