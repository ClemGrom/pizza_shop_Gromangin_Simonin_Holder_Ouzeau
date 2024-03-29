<?php

namespace pizzashop\auth\api\app\actions;

use pizzashop\auth\api\app\renderer\JSONRenderer;
use pizzashop\auth\api\exceptions\TokenExpirerException;
use pizzashop\auth\api\exceptions\TokenIncorrectException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * action qui permet de valider un token
 */
class ValidateUserAction
{

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        // récupération du token donné indiqué dans le header de la requête
        $token = $rq->getHeaderLine('Authorization');
        $token = explode(' ', $token);

        try {
            // on tente de valider le token
            $connexion = $this->container->get('auth.service')->validate($token[1]);

            // si le token est valide, on retourne un code 200 et l'utilisateur
            $data = [
                'user' => $connexion
            ];
            $code = 200;

        } catch (TokenExpirerException|TokenIncorrectException $e) {
            // si le token est invalide ou expiré, on retourne un code 401 et un message d'erreur
            $data = [
                "message" => "401 Unauthorized",
                "exception" => [[
                    "type" => "Slim\\Exception\\HttpUnauthorizedException",
                    "message" => $e->getMessage(),
                    "code" => $e->getCode(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                ]]
            ];
            $code = 401;
        } catch (\Exception $e) {
            // si une autre exception est levée, on retourne un code 500 et un message d'erreur
            $data = [
                "message" => "500 Internal Server Error",
                "exception" => [[
                    "type" => "Slim\\Exception\\HttpInternalServerErrorException",
                    "message" => $e->getMessage(),
                    "code" => $e->getCode(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                ]]
            ];
            $code = 500;
        }

        // on retourne la réponse avec le code et les données
        return JSONRenderer::render($rs, $code, $data)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET' )
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Content-Type', 'application/json');
    }
}

