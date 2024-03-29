<?php

namespace pizzashop\shop\app\actions;

use GuzzleHttp\Client;
use pizzashop\shop\app\renderer\JSONRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConnectionAction permet de se connecter depuis l'api commande
 */
class ConnectionAction
{

    private Client $guzzle;

    public function __construct(Client $guzzleBaseUri)
    {
        $this->guzzle = $guzzleBaseUri;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {

        // récupération du header Authorization
        $authorizationHeader = $rq->getHeaderLine('Authorization');

        // requête vers l'api users
        try {
            $data = $this->guzzle->request('POST', "/api/users/signin", [
                'headers' => [
                    'Authorization' => $authorizationHeader
                ]
            ]);
            $data = json_decode($data->getBody()->getContents(), true);
            $code = 200;
        } catch (\Exception $e) {
            $data = [
                "error" => $e->getMessage(),
                "code" => $e->getCode()
            ];
            $code = 500;
        }

        // retour de la réponse
        return JSONRenderer::render($rs, $code, $data)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'POST' )
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Content-Type', 'application/json');
    }
}


