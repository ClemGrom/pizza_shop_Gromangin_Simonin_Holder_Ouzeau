import { serviceCommande } from "../services/ServiceCommande.js";

export class getCommandesAction {
    commandeService = null;

    constructor() {
        this.commandeService = new serviceCommande();
    }

    async execute() {
        return JSON.stringify(await this.commandeService.getCommandes());
    }
}