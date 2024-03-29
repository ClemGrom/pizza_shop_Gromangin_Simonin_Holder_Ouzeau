import express from "express";
import helmet from "helmet";
import { getCommandeIDAction } from "../action/getCommandeIDAction.js";
import { getCommandesAction } from "../action/getCommandesAction.js";
import { changeCommandeState } from "../action/changeCommandeState.js";

/**
 * Classe qui gère les routes de l'application
 */
export class middleware {

    app = null;
    port = null;

    constructor() {
        this.app = express();
        this.port = process.env.PORT || 3000; // port par défaut à 3000 si non défini dans les variables d'environnement
        this.app.use(express.json());
        this.app.use(express.urlencoded({extended: false}));
        this.app.use(helmet());

        // Routes qui permet de récupérer toutes les commandes
        this.app.get('/commandes', async (req, res, next) => {
            try {
                const actionCommandes = new getCommandesAction();
                res.send(await actionCommandes.execute());
            } catch (err) {
                console.log(err);
                next(err);
            }
        })

        // Routes qui permet de récupérer une commande par son id
        this.app.get('/commandes/:id', async (req, res, next) => {
            try {
                const actionCommandeID = new getCommandeIDAction();
                res.send(await actionCommandeID.execute(req.params.id));
            } catch (err) {
                console.log(err);
                next(err);
            }
        })

        // Routes qui permet de changer l'état d'une commande
        this.app.patch('/commandes/:id', async (req, res, next) => {
            try {
                const actionChangeCommandeState = new changeCommandeState();
                res.send(await actionChangeCommandeState.execute(req.params.id));
            } catch (err) {
                console.log(err);
                next(err);
            }
        })

        // Routes de base qui renvoie un message quand l'app est lancée
        this.app.listen(this.port, () => {
            console.log(`App ready, listening on ${this.port}`)
        });
    }
}