<?php
namespace App\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\View\View;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Urls;
use App\Repository\UrlsRepository;

use App\Entity\User;
use App\Repository\UserRepository;
/**
 * @Route("/api", name="api")
 */
class APIController extends FOSRestController
{
  private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    /**
    * @Rest\Get("/urls")
    */
   public function getUrlList(UrlsRepository $repository)
   {
    $list = $repository->findAll();
    $result = $this->serializer->serialize($list, 'json');
        
    return new JsonResponse($result, 200, [], true);
   }
   /**
   * @Rest\Get("/urls_N/{count}")
   */
  public function getNUrlList($count, UrlsRepository $repository)
  {
   $list = $repository->findLastUrls($count);
   $result = $this->serializer->serialize($list, 'json');
       
   return new JsonResponse($result, 200, [], true);
  }
   /**
   * @Rest\Get("/urls/{shortUrl}")
   */
  public function getUrl($shortUrl, UrlsRepository $repository)
  {
   $response = $repository->findByShortUrl($shortUrl);
   $result = $this->serializer->serialize($response, 'json');
       
   return new JsonResponse($result, 200, [], true);
  }
  
  /**
  * @Rest\Post("/create_url")
  */
  public function createUrl(Request $request, UrlsRepository $repository)
  {
    
    $data = json_decode($request->getContent());
    $response = array();
    $response['status'] = 'ok';
    $response['errors'] = array();

    if($data):
      if(!isset($data->url)):
        $response['status'] = 'error';
        $response['errors']['url'] = 'Url is empty';
      elseif(!$data->url):
        $response['status'] = 'error';
        $response['errors']['url'] = 'Url is empty';
      endif;
      if (!$this->url_exists($data->url)) {
        $response['status'] = 'error';
        $response['errors']['url'] = 'Incorrect Url';
      }
      if(!isset($data->shortUrl)):
        $response['status'] = 'error';
        $response['errors']['shortUrl'] = 'Short Url is not set';
      elseif($data->shortUrl && $repository->findByShortUrl($data->shortUrl)):
        $response['status'] = 'error';
        $response['errors']['shortUrl'] = 'Short Url is already used';
      endif;
      if( $response['status'] == 'ok'):
        if (!$data->shortUrl):
          $data->shortUrl = $this->randomString(5);
        endif;
        while($repository->findByShortUrl($data->shortUrl)):
          $data->shortUrl = $this->randomString(5);
        endwhile;

        $url = new Urls();
        $url->setUrl($data->url);
        $url->setShortUrl($data->shortUrl);

        if(isset($data->token)):
          if($data->token):
            $user = $this->getDoctrine()->getRepository(User::class)->findByApiKey($data->token);
          endif;
        endif;
        if(isset($user)) $url->setUserId($user->getId());
        else $url->setUserId(-1);
        $url->setUsageCount(0);
        $url->setCreationDate(date_create(date('m/d/Y h:i:s a', time())));
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($url);
        $entityManager->flush();
        
        $response['url'] = $url;
      endif;
    else:
      $response['status'] = 'error';
      $response['errors']['data'] = 'Empty Request';
    endif;
    $responseJSON = $this->serializer->serialize($response, 'json');
    return new JsonResponse($responseJSON, 200, [], true);
  }


    /**
  * @Rest\Post("/sign_up")
  */
  public function createUser(Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder)
  {
    $data = json_decode($request->getContent());
    
    $response = array();
    $response['status'] = 'ok';
    $response['errors'] = array();
    if($data):
      if(isset($data->email)):
        if(filter_var($data->email, FILTER_VALIDATE_EMAIL)):
          if($repository->findByEmail($data->email)):
            $response['status'] = 'error';
            $response['errors']['email'] = 'Email is used';
          endif;
        else:
          $response['status'] = 'error';
          $response['errors']['email'] = 'Email is not valid';
        endif;
      else:
        $response['status'] = 'error';
        $response['errors']['email'] = 'Empty Email';
      endif;

      if(!isset($data->username)):
        $response['status'] = 'error';
        $response['errors']['username'] = 'Empty Username';
      elseif(!$data->username):
        $response['status'] = 'error';
        $response['errors']['username'] = 'Empty Username';
      endif;

      if(!isset($data->password)):
        $response['status'] = 'error';
        $response['errors']['password'] = 'Empty Password';
      elseif(!$data->password):
        $response['status'] = 'error';
        $response['errors']['password'] = 'Empty Password';
      endif;

      if($response['status'] == 'ok'):
        $user = new User();
        $user->setUsername($data->username);
        $user->setEmail($data->email);
        $user->setRoles(array('user'));
        $user->setPassword(
          $passwordEncoder->encodePassword(
            $user,
            $data->password
          )
        );
        $user->setApiKey(
          $passwordEncoder->encodePassword(
            $user,
            $data->email
          )
        );
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $response['userKey'] = $user->getApiKey();
      endif;

    else:
      $response['status'] = 'error';
      $response['errors']['data'] = 'Empty Request';
    endif;

      $responseJSON = $this->serializer->serialize($response, 'json');
    return new JsonResponse($responseJSON, 200, [], true);;
  }


    /**
  * @Rest\Post("/login")
  */
  public function login(Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder)
  {
    $data = json_decode($request->getContent());
    
    $response = array();
    $response['status'] = 'ok';
    $response['errors'] = array();
    if($data):
      if(isset($data->email)):
        if(!filter_var($data->email, FILTER_VALIDATE_EMAIL)):
          $response['status'] = 'error';
          $response['errors']['email'] = 'Email is not valid';
        endif;
      else:
        $response['status'] = 'error';
        $response['errors']['email'] = 'Empty Email';
      endif;

      if(!isset($data->password)):
        $response['status'] = 'error';
        $response['errors']['password'] = 'Empty Password';
      elseif(!$data->password):
        $response['status'] = 'error';
        $response['errors']['password'] = 'Empty Password';
      endif;

      if($response['status'] == 'ok'):
        if(!$user = $repository->findByEmail($data->email)):
          $response['status'] = 'error';
          $response['errors']['email'] = 'Email is not registered';
        elseif($passwordEncoder->isPasswordValid($user,$data->password)):
          $response['userKey'] = $user->getApiKey();
        else:
          $response['status'] = 'error';
          $response['errors']['password'] = 'Not valid password';
        endif;
      endif;
    else:
      $response['status'] = 'error';
      $response['errors']['data'] = 'Empty Request';
    endif;

    $responseJSON = $this->serializer->serialize($response, 'json');
    return new JsonResponse($responseJSON, 200, [], true);;
  }

  /**
   * @Rest\Post("/user")
   */
  public function getUserInfo(Request $request, UserRepository $repository)
  {

    $data = json_decode($request->getContent());
    if($data->userKey):
      $result = $repository->findByApiKey($data->userKey);
      if($result) :
        $response['status'] = 'ok';
        $response['user']['username'] = $result->getUsername();
        $response['user']['email'] = $result->getEmail();
        $response['user']['id'] = $result->getId();
      else:
        $response['status'] = 'error';
      endif;
    else:
      $response['status'] = 'ok';
      $response['user']['username'] = '';
      $response['user']['email'] = '';
      $response['user']['id'] = '';
    endif;
    $resultJSON = $this->serializer->serialize($response, 'json');
        
    return new JsonResponse($resultJSON, 200, [], true);
  }

  /**
   * @Rest\Post("/user_urls")
   */
  public function getUserUrl(Request $request, UserRepository $repository, UrlsRepository $repositoryUrls)
  {

    $data = json_decode($request->getContent());
    if($data->userKey):
      $result = $repository->findByApiKey($data->userKey);
      if($result) :
        $response['status'] = 'ok';
        $response['user']['username'] = $result->getUsername();
        $response['user']['email'] = $result->getEmail();
        $response['user']['id'] = $result->getId();
        $response['user']['urls'] = $repositoryUrls->findByUserId($result->getId());
      else:
        $response['status'] = 'error';
      endif;
    else:
      $response['status'] = 'ok';
      $response['user']['username'] = '';
      $response['user']['email'] = '';
      $response['user']['id'] = '';
    endif;
    $resultJSON = $this->serializer->serialize($response, 'json');
        
    return new JsonResponse($resultJSON, 200, [], true);
  }

  function randomString($length)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $length; $i++) {
        $randstring .= $characters[rand(0, strlen($characters)-1)];
    }
    return $randstring;
  }
  function url_exists($url) {
    $headers = @get_headers($url); 
  
    if($headers && strpos( $headers[0], '200')) { 
      return true;
    } 
    else { 
      return false; 
    } 
  }
}
