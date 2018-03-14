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


class BookPageAction implements ServerMiddlewareInterface
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

       $url = "https://www.googleapis.com/books/v1/volumes/";

        $id =  $request->getAttribute('id');
        $book = [];

        if($id)
        {
            $searchurl = $url.$id;
            $ch = curl_init($searchurl);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER ,true);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER  ,false);
            $search = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $book = $search;

            if(isset($search['error'])){
                $book = ['error' => 'error'];
            }
            else
            {
                $vol = $book['volumeInfo'];

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

                $imageurl = '';
                switch(true){
                    case  isset($vol['imageLinks']['small']):
                        $imageurl = $vol['imageLinks']['small'];
                        break;
                    case  isset($vol['imageLinks']['thumbnail']):
                        $imageurl = $vol['imageLinks']['thumbnail'];
                        break;
                    case  isset($vol['imageLinks']['smallThumbnail']):
                        $imageurl = $vol['imageLinks']['smallThumbnail'];
                        break;

                }


                $book = 
                    [
                      'title' => isset($vol['title']) ? $vol['title'] : '',
                      'subtitle' => isset($vol['subtitle']) ? $vol['subtitle'] : '',
                      'authors' => isset($vol['authors']) ? implode("<br><br>", $vol['authors']) : '',
                      'description' => isset($vol['description']) ? $vol['description'] : '',
                      'printedPageCount' => isset($vol['printedPageCount']) ? $vol['printedPageCount'] : '',
                      'pagecount' => isset($vol['pageCount']) ? $vol['pageCount'] : '',
                      'publisher' => isset($vol['publisher']) ? $vol['publisher'] : '',
                      'publishedDate' => isset($vol['publishedDate']) ? $vol['publishedDate'] : '',
                      'isbns' => $isbns,
                      'printType' => isset($vol['printType']) ? $vol['printType'] : '',
                      'listPrice' => isset($book['saleInfo']['listPrice']) ? $book['saleInfo']['listPrice']['amount']. ' '. $book['saleInfo']['listPrice']['currencyCode']: 'Not Listed',
                      'image' => $imageurl ,

                        
                    ];
                
                
            }
            


        }


        return new HtmlResponse($this->template->render('app::book-page', ['book' => $book, 'rawbook' => $search]));
    }
}
