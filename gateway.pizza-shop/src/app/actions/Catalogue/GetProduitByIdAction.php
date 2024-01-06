<?php

namespace pizzashop\gateway\app\actions\Catalogue;

use GuzzleHttp\Exception\GuzzleException;
use pizzashop\gateway\app\renderer\GuzzleRequest;
use pizzashop\gateway\app\renderer\JSONRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GetProduitByIdAction permet d'obtenir un produit par son id
 */
class GetProduitByIdAction
{
    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args)
    {
        // TODO produit est rendu en double
        try {
            $data = GuzzleRequest::MakeRequest('GET', 'catalogue', "produits/" . $args['id']);
            $code = 200;
        } catch (GuzzleException $e) {
            $data = [
                "error" => $e->getMessage(),
                "code" => $e->getCode()
            ];
            $code = 500;
        }

        // retourne les produits
        return JSONRenderer::render($rs, $code, $data)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET' )
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Content-Type', 'application/json');
    }
}

