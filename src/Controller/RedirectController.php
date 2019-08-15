<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Urls;
use App\Repository\UrlsRepository;
use App\Entity\UrlHistory;
use App\Repository\UrlHistoryRepository;

class RedirectController extends AbstractController
{
    /**
     * @Route("/{url}", name="redirect")
     */
    public function index($url, Request $request)
    {
        $result = $this->getDoctrine()->getRepository(Urls::class)->findByShortUrl($url);
        var_dump($result);
        if($result){
            $entityManager = $this->getDoctrine()->getManager();

            $row = new UrlHistory();
            $row->setUrl($result->getUrl());
            $row->setShortUrl($result->getShortUrl());
            $row->setUsageIp($request->getClientIp());
            $row->setUsageDate(date_create(date('m/d/Y h:i:s a', time())));
    
            $entityManager->persist($row);
        
            $result->setUsageCount( $result->getUsageCount() + 1 );
            
            $entityManager->flush();
            $url = $result->getUrl();
            if(substr_count($url, 'http://') || substr_count($url, 'https://')) :
                return $this->redirect($url);
            else :
                return $this->redirect('http://'.$url);
            endif;
        } else {
            // return $this->redirect('/');
        }
    }
}
