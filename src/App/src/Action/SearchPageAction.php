<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Expressive\Plates\PlatesRenderer;


class SearchPageAction implements ServerMiddlewareInterface
{
    private $router;

    private $template;

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template = null)
    {
        $this->router   = $router;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {


        $apikey = "AIzaSyCqk49mlAXy8kXSe81C_wib3nsFnEr1-S0";
        $url = "https://www.googleapis.com/books/v1/volumes?q=";

        $data =  $request->getParsedBody();
        $books = [];

        if($data['params'])
        {
            $searchurl = $url.urlencode($data['params'])."&key=".$apikey;
            $ch = curl_init($searchurl);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER ,true);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER  ,false);
            $search = json_decode(curl_exec($ch), true);
            curl_close($ch);

           if(isset($search['items'])) {
               foreach ($search['items'] as $book) {
                   $vol = $book['volumeInfo'];
                   $id = $book['id'];
                   $title = isset($vol['title']) ? $vol['title'] : '';
                   $subtitle = isset($vol['subtitle']) ? $vol['subtitle'] : ''; //$book->volumeInfo->subtitle;
                   $authors = isset($vol['authors']) ? implode("<br><br>", $vol['authors']) : '';
                   $description = isset($vol['description']) ? $vol['description'] : '';
                   $pagecount = isset($vol['pageCount']) ? $vol['pageCount'] : '';
                   $isbns = [];

                   if(isset($vol['industryIdentifiers']))
                    foreach($vol['industryIdentifiers'] as $isbn){
                       switch ($isbn['type']){
                           case 'ISBN_13':
                               $isbns[] = 'ISBN_13: '. $isbn['identifier'];
                               break;
                           case 'ISBN_10':
                               $isbns[] = 'ISBN_10: '. $isbn['identifier'];
                               break;
                       }
                    }
                    $isbns = implode('<br>', $isbns);

                   $books[] = [
                       'id' => $id,
                       'title' => $title,
                       'subtitle' => $subtitle,
                       'authors' => $authors,
                       'description' => $description,
                       'pagecount' => $pagecount,
                       'isbns' => $isbns,
                   ];
               }
           }


        }
        else
        {

            $books = [];
        }


        return new HtmlResponse($this->template->render('app::home-page', ['data' => $data,'books' => $books, ]));
    }
}
