<?php

namespace pizzashop\shop\domain\service\commande;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use pizzashop\shop\domain\dto\commande\CommandeDTO;
use pizzashop\shop\domain\entities\commande\Commande;
use pizzashop\shop\domain\entities\commande\Item;
use pizzashop\shop\domain\service\exception\ServiceCommandeInvalidItemException;
use pizzashop\shop\domain\service\exception\ServiceCommandeInvalidTransitionException;
use pizzashop\shop\domain\service\exception\ServiceCommandeInvialideException;
use pizzashop\shop\domain\service\exception\ServiceCommandeNotFoundException;
use pizzashop\shop\domain\service\exception\ServiceProduitNotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;
use GuzzleHttp\Client;

/**
 *  Service de gestion des commandes
 */
class ServiceCommande implements icommande
{

    private LoggerInterface $logger;
    private Client $guzzle;

    /**
     * ServiceCommande constructor qui prend en paramètre le logger
     * @param LoggerInterface $logger
     */
    function __construct(LoggerInterface $logger, Client $guzzle)
    {
        $this->logger = $logger;
        $this->guzzle = $guzzle;
    }

    /**
     * Valide les données d'une commande
     * @throws ServiceCommandeInvialideException
     */
    function validerDonneesDeCommande(CommandeDTO $commandeDTO): void
    {
        try {
            v::attribute('mail_client', v::email())
                ->attribute('type_livraison', v::intVal()->between(Commande::LIVRAISON_SUR_PLACE, Commande::LIVRAISON_A_DOMICILE))
                ->attribute('items', v::arrayVal()->notEmpty()
                    ->each(v::attribute('numero', v::intVal()->positive())
                        ->attribute('taille', v::in([1, 2]))
                        ->attribute('quantite', v::intVal()->positive())
                    ))
                ->assert($commandeDTO);
        } catch (NestedValidationException $e) {
            echo $e->getFullMessage();
            throw new ServiceCommandeInvialideException($e);
        }
    }

    /**
     * Retourne une commande en fonction de son UUID
     * @param string $UUID
     * @return CommandeDTO
     * @throws ServiceCommandeNotFoundException
     */
    function accederCommande(string $UUID): CommandeDTO
    {
        try {
            $commande = Commande::where('id', '=', $UUID)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ServiceCommandeNotFoundException($UUID);
        }
        return $commande->toDTO();
    }

    /**
     * Valide une commande en fonction de son UUID
     * @param string $UUID
     * @return CommandeDTO
     * @throws ServiceCommandeInvalidTransitionException
     * @throws ServiceCommandeNotFoundException
     */
    function validationCommande(string $UUID): CommandeDTO
    {
        try {
            $commande = Commande::where('id', $UUID)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ServiceCommandeNotFoundException($UUID);
        }
        if ($commande->etat >= Commande::ETAT_VALIDE) {
            throw new ServiceCommandeInvalidTransitionException($UUID);
        }
        $commande->update(['etat' => Commande::ETAT_VALIDE]);
        $this->logger->info("Commande $UUID validée");
        return $commande->toDTO();
    }

    /**
     * Créer une commande à partir d'un CommandeDTO
     * @param CommandeDTO $commandeDTO
     * @return CommandeDTO
     * @throws ServiceCommandeInvalidItemException
     */
    function creerCommande(CommandeDTO $commandeDTO): CommandeDTO
    {
        // Générer un UUID
        $commandeDTO->id = Uuid::uuid4();
        $commandeDTO->date_commande = date('Y-m-d H:i:s');
        $commandeDTO->etat = Commande::ETAT_CREE;
        $commandeDTO->delai = 0;

//        $this->validerDonneesDeCommande($commandeDTO);

        // créer la commande
        $commande = Commande::create([
            'id' => $commandeDTO->id,
            'date_commande' => $commandeDTO->date_commande,
            'type_livraison' => $commandeDTO->type_livraison,
            'etat' => $commandeDTO->etat,
            'mail_client' => $commandeDTO->mail_client,
            'delai' => $commandeDTO->delai,
        ]);
        // créer les items
        foreach ($commandeDTO->items as $itemDTO) {
            $response = $this->guzzle->request('GET', "/api/produits/" . $itemDTO['numero']);
            $data = json_decode($response->getBody()->getContents(), true);

            $itemResTaille = array();
            foreach ($data["tailles"] as $item) {
                if($item["id_taille"] == $itemDTO['taille'])
                    $itemResTaille = [
                        'libelle_taille' => $item["libelle_taille"],
                        'tarif' => $item["tarif"],
                    ];
            }
            $itemResTaille['libelle_produit'] = $data["libelle_produit"];

            $item = new Item();
            $item->numero = $itemDTO['numero'];
            $item->taille = $itemDTO['taille'];
            $item->quantite = $itemDTO['quantite'];
            $item->libelle_taille = $itemResTaille["libelle_taille"];
            $item->tarif = $itemResTaille["tarif"];
            $item->libelle = $itemResTaille["libelle_produit"];
            $commande->items()->save($item);
        }
        $commande->calculerMontantTotal();
        $commande->save();

        $this->logger->info("Commande $commande->id créée");
        return $commande->toDTO();
    }

}