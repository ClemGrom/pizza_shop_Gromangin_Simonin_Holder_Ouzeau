<?php

namespace pizzashop\tests\commande;

use Faker\Factory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use pizzashop\shop\domain\entities\catalogue\Taille;
use pizzashop\shop\domain\entities\commande\Commande;
use pizzashop\shop\domain\entities\commande\Item;
use Illuminate\Database\Capsule\Manager as DB;

class ServiceCommandeTest extends \PHPUnit\Framework\TestCase {

    private static $commandeIds = [];
    private static $itemIds = [];
    private static $serviceProduits;
    private static $serviceCommande;
    private static $faker;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $dbcom = __DIR__ . '/../../config/commande.db.test.ini';
        $dbcat = __DIR__ . '/../../config/catalog.db.ini';
        $db = new DB();
        $db->addConnection(parse_ini_file($dbcom), 'commande');
        $db->addConnection(parse_ini_file($dbcat), 'catalog');
        $db->setAsGlobal();
        $db->bootEloquent();

        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler('/../../config/log/logFile.txt', Logger::INFO));
        echo __DIR__ . '../../config/log/logFile.txt';
        self::$serviceProduits = new \pizzashop\shop\domain\service\catalogue\ServiceCatalogue();
        self::$serviceCommande = new \pizzashop\shop\domain\service\commande\ServiceCommande(self::$serviceProduits, $logger);
        self::$faker = Factory::create('fr_FR');
        self::fillDB();
        print_r(self::$commandeIds);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDown(); 
        self::cleanDB();
    }


    private static function cleanDB(){
        foreach (self::$commandeIds as $id){
            Commande::find($id)->delete();
        }
        foreach (self::$itemIds as $id){
            Item::find($id)->delete();
        }
    }
    private static function fillDB() {

        for ($i = 0; $i < 5; $i++) {
            $commande = new \pizzashop\shop\domain\entities\commande\Commande();
            $commande->id = self::$faker->numberBetween(1000, 100000000000);
            $commande->type_livraison = self::$faker->randomElement([
                Commande::LIVRAISON_SUR_PLACE, Commande::LIVRAISON_A_EMPORTER, Commande::LIVRAISON_A_DOMICILE
            ]);
            $fake_date = self::$faker->dateTimeBetween('-1 year', 'now');
            $commande->date_commande = self::$faker->dateTimeBetween('-1 year', 'now')
                ->format('Y-m-d H:i:s');
            $commande->etat = Commande::ETAT_CREE;
            $commande->mail_client = self::$faker->firstName . self::$faker->lastName . '@' . self::$faker->freeEmailDomain;
//            $commande->save();
            self::$commandeIds[] = $commande->id;

            /**s
             * des items
             */
            $nbItems = self::$faker->numberBetween(1, 5);
            for ($j = 0; $j < 3; $j++) {
                $item = new \pizzashop\shop\domain\entities\commande\Item();
                $numero = self::$faker->numberBetween(1, 10);
                $taille = self::$faker->randomElement([1, 2]);

                $produit = self::$serviceProduits->getProduit($numero, $taille);

                $item->numero = $numero;
                $item->libelle = $produit->libelle_produit;
                $item->taille = $taille;
                $item->tarif = $produit->tarif;
                $item->quantite = self::$faker->numberBetween(1, 5);

                $tls = Taille::where('id', '=', $taille)->first();
                $item->libelle_taille = $tls->libelle;

                $commande->items()->save($item);
//                $commande->save();
                self::$itemIds[] = $item->id;
            }
            $commande->calculerMontantTotal($commande->id);
            $commande->save();
        }
    }


    public function testGetCommande(){
        //$id = self::$commandeIds[0];
        foreach (self::$commandeIds as $id){
            $commandeEntity = Commande::find($id);
            $commandeDTO = self::$serviceCommande->accederCommande($id);

            self::$serviceCommande->validationCommande($id);

            $this->assertNotNull($commandeDTO);
            $this->assertEquals($id, $commandeDTO->id);
            $this->assertEquals($commandeEntity->mail_client, $commandeDTO->mail_client);
            $this->assertEquals($commandeEntity->etat, $commandeDTO->etat);
            $this->assertEquals($commandeEntity->type_livraison, $commandeDTO->type_livraison);
            $this->assertEquals($commandeEntity->montant_total, $commandeDTO->montant);
            $this->assertEquals(count($commandeEntity->items), count($commandeDTO->items));
        }


    }

}