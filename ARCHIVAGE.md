# Suivi d'archivage des projets

Objectif : archiver au moins un projet par jour dans archiveCowprod.

## Statuts

- **À qualifier** : il faut décider si le dossier correspond à un projet archivable et à quelle granularité.
- **À faire** : projet faisable et prêt à être archivé.
- **Reporté** : projet identifié mais non faisable pour le moment (ASP, CD-ROM, dépendance technique, données manquantes, AF / à fouiller, etc.).
- **Archivé** : projet traité dans archiveCowprod.
- **Ignoré** : sauvegarde, doublon, dossier technique ou élément qui ne doit pas devenir un projet.

## File d'attente

| Groupe / client | Dossier source | Projet candidat | Technologies | Statut | Notes |
|---|---|---|---|---|---|
| A la folie | `A la folie/2019_bandOrganizer` | BandOrganizer | Node.js + clients Android/iOS | Ignoré | Variante de BandOrganizer ; ensemble BandOrganizer exclu de la file |
| A la folie | `A la folie/20230504_BO` | BandOrganizer | | Ignoré | Sauvegarde/version |
| A la folie | `A la folie/BO` | BandOrganizer | | Ignoré | Même projet |
| A la folie | `A la folie/BOBAKCUP` | BandOrganizer | | Ignoré | Backup |
| A la folie | `A la folie/DEL_affiche.legroupealafolie.org` | affiche.legroupealafolie.org | | Archivé | En ligne sur https://affiche.legroupealafolie.org.archive.cowprod.net/ ; J2 réussi |
| A la folie | `A la folie/DEL_demo.legroupealafolie.org` | demo.legroupealafolie.org | | À faire | Seul dossier DEL présent : à traiter comme projet |
| A la folie | `A la folie/Telecaster` | Telecaster | | Ignoré | Pas un projet informatique |
| A la folie | `A la folie/bandorganizer` | BandOrganizer | | Ignoré | Version principale ; ensemble BandOrganizer exclu |
| A la folie | `A la folie/bandorganizer_bck` | BandOrganizer | | Ignoré | Backup |
| A la folie | `A la folie/bandorganizer_client_old` | BandOrganizer | | Ignoré | Ancien client |
| A la folie | `A la folie/bandorganizer_del` | BandOrganizer | | Ignoré | Ancienne version |
| A la folie | `A la folie/conductor` | BandOrganizer | | Ignoré | Préversion de BandOrganizer |
| A la folie | `A la folie/tempBO` | BandOrganizer | | Ignoré | Temporaire BandOrganizer |
| Arte Factory | `Arte Factory/Bezons Coeur de ville` | Bezons Coeur de ville | Android | Reporté | |
| Arte Factory | `Arte Factory/Teaser Montparnasse` | Teaser Montparnasse | Android | Reporté | |
| Bobigny | `Bobigny/Bobigny` | Bobigny | | Reporté | Projet listé individuellement |
| Christian | `Christian/CM - Musique` | CM - Musique | Node.js | Reporté | |
| Christian | `Christian/Famille` | Famille | ADO | Reporté | À reprendre plus tard |
| Christian | `Christian/Pour l'histoire` | Pour l'histoire | PHP | À faire | `pourlhistoire` correspond au même projet |
| Christian | `Christian/pourlhistoire` | Pour l'histoire | PHP | Ignoré | Doublon du projet Pour l'histoire |
| Delmont Imaging | `Delmont Imaging` | imagynServer et projets Delmont Imaging | Node.js ; possible POC iOS | Reporté | Plusieurs versions datées d'IMAGYN_SERVER ; client à reprendre globalement |
| Dentsu | `Dentsu/Salle du board` | Salle du board | Node.js | Reporté | |
| Dentsu | `Dentsu/Temperature` | Temperature | Node.js | Reporté | |
| Dossier404 | `Dossier404/Ciel` | Ciel | ASP | Reporté | Premier projet d'indépendant |
| Dossier404 | `Dossier404/contact` | contact | | Ignoré | Administratif |
| F.O. | `F.O./FNEM` | FNEM | Domotique | Reporté | À conserver comme archive documentaire/galerie ; pas à réinstaller |
| F.O. | `F.O./FO Photos & plan Salle de réunion` | Salle de réunion | Documentation / domotique | Reporté | Matériau de galerie à conserver ; pas à réinstaller |
| F.O. | `F.O./Plans 3D FO Salle de réunion` | Salle de réunion | Documentation / 3D | Reporté | Matériau de galerie à conserver ; pas à réinstaller |
| F.O. | `F.O./baie` | baie | Domotique | Reporté | À conserver comme archive documentaire/galerie ; pas à réinstaller |
| F.O. | `F.O./intranet` | intranet | | Ignoré | Dossier vide |
| F.O. | absent de l'inventaire | Annuaire | Microsoft 365 | Reporté | Projet à identifier ; difficile à réinstaller |
| LAPSA | `LAPSA/DEL_backoffice.lapsa.cowprod.net` | backoffice.lapsa.cowprod.net | | À faire | À réinstaller |
| LAPSA | `LAPSA/DEL_webapp.lapsa.cowprod.net` | webapp.lapsa.cowprod.net | | À faire | À réinstaller |
| LAPSA | `LAPSA/20191007_LAPSA`, `20211112_LAPSA`, `ARRET 20230913` | LAPSA versions | | Ignoré | Versions/sauvegardes, pas des projets distincts |
| LAPSA | absent de l'inventaire | Statistiques | | Reporté | Projet à retrouver |
| Listee | `Listee/listee app` | Listee App mobile | Mobile | À qualifier | Seul projet à retenir de cet inventaire ; d'autres projets Listee existent mais sont absents ici |
| Listee | autres dossiers visibles sous `Listee` | — | | Ignoré | Ne constituent pas des projets distincts à retenir |
| Paix Liturgique | `Paix Liturgique/Application Clio - version 2004_files` | Intranet Clio | ASP | Reporté | |
| Paix Liturgique | `Paix Liturgique/FACTURES OVH` | — | | Ignoré | Administratif |
| Paix Liturgique | `Paix Liturgique/PSBL spamtrap mail for 88.191.146.26_files` | — | | Ignoré | Archive technique |
| Paix Liturgique | `Paix Liturgique/Textus` | Textus | ASP | Reporté | |
| Paix Liturgique | `Paix Liturgique/dale` | dale | ASP | Reporté | |
| Paix Liturgique | `Paix Liturgique/mailsfromxxx.graffle` | — | | Ignoré | Documentation |
| Paix Liturgique | `Paix Liturgique/paixliturgiquea` | paixliturgiquea | ASP | Reporté | |
| Paix Liturgique | `Paix Liturgique/paixliturgiqueb` | paixliturgiqueb | ASP | Reporté | |
| Paix Liturgique | `Paix Liturgique/sms2http` | sms2http | Android (à confirmer) | Reporté | |
| Perso | `Perso/Akoris` | Akoris | | Reporté | AF ; peut contenir plusieurs projets |
| Perso | `Perso/Sombrero` | Sombrero | | Reporté | AF ; nom de serveur |
| Perso | `Perso/advanced-ip` | advanced-ip | | Reporté | AF |
| Perso | `Perso/cp2` | cp2 | | Reporté | AF ; nom de serveur |
| Perso | `Perso/exchange` | exchange | | Reporté | AF ; probablement archive d'un serveur Exchange |
| Perso | `Perso/fiesta` | fiesta | | Reporté | AF ; nom de serveur |
| Perso | `Perso/forge` | forge | | Reporté | AF ; nom de serveur |
| Perso | `Perso/forum` | forum | | Reporté | AF ; conteneur/client possible avec plusieurs projets |
| Perso | `Perso/imagesdiverses` | — | | Ignoré | Archives d'images à préserver, pas un projet |
| Perso | `Perso/manu sur machine ludo` | manu sur machine ludo | | Reporté | AF ; archive serveur/machine |
| Perso | `Perso/moo` | moo | | Reporté | AF ; nom de serveur |
| Perso | `Perso/sitetavu` | sitetavu | | Reporté | AF |
| Perso | `Perso/universel` | universel | | Reporté | AF |
| Perso | `Perso/vj` | vj | | Reporté | AF |
| Perso | `Perso/wwwroot` | wwwroot | | Reporté | AF |
| Piaraly | `Piaraly/AccesDiamondJubileeFrance` | AccesDiamondJubileeFrance | iOS | Reporté | |
| Piaraly | `Piaraly/Carte grise` | Carte grise | | À faire | Site à réinstaller |
| Piaraly | `Piaraly/Courcell (1)` | Courcell | | Reporté | AF |
| Piaraly | `Piaraly/Epilspace` | Epilspace | | Reporté | AF |
| Piaraly | `Piaraly/FACTURES` | FACTURES | ASP | Reporté | Projet ASP malgré le nom |
| Piaraly | `Piaraly/LAVAGE (1)` | LAVAGE | ASP | Reporté | |
| Piaraly | `Piaraly/MESSAGE CENTRE SPEEDY` | MESSAGE CENTRE SPEEDY | | Reporté | AF ; probablement téléphonie/message de répondeur |
| Piaraly | `Piaraly/Malesherbes` | Malesherbes | | Reporté | AF |
| Piaraly | `Piaraly/Mulaqat` | Mulaqat | iOS (à confirmer) | Reporté | AF |
| Piaraly | `Piaraly/Mulaqat France` | Mulaqat | | Reporté | AF ; lié à Mulaqat, pas un projet distinct |
| Piaraly | `Piaraly/Parking` | Parking | ASP | Reporté | |
| Piaraly | `Piaraly/Rendez-vous 2018-06-20 18-19-02`, `Rendez-vous 2018-06-20 18-20-25`, `Rendez-vous 2019-06-03 13-57-46`, `Rendez-vous 2020-06-18 16-08-11` | Rendez-vous | ASP | Reporté | Versions/sauvegardes d'un même projet |
| Piaraly | `Piaraly/TESTMulaqat France` | Mulaqat | | Reporté | AF ; variante de test liée à Mulaqat, pas un projet distinct |
| Piaraly | `Piaraly/clichy` | clichy | | Reporté | AF |
| Piaraly | `Piaraly/facture.cowprod.net.bbprojectd` | facture.cowprod.net | ASP | Reporté | |
| Piaraly | `Piaraly/location-voiture.com` | location-voiture.com | ASP (à confirmer) | Reporté | |
| Piaraly | `Piaraly/nicou50ans.cowprod.net` | nicou50ans.cowprod.net | | Ignoré | Pas nécessaire de réinstaller |
| Piaraly | `Piaraly/pbclichy` | pbclichy | | Reporté | AF |
| Piaraly | `Piaraly/soisy.numbers` | — | Apple Numbers | Ignoré | Simple document, pas un projet |
| Piaraly | `Piaraly/soisy.pages` | — | Apple Pages | Ignoré | Simple document, pas un projet |
| Test | `Test/www.az-production.fr` | www.az-production.fr | | À faire | À réinstaller |
| Test | `Test/www.az-quad.fr` | www.az-quad.fr | | À faire | À réinstaller |
| Test | `Test/www.discobus.fr` | www.discobus.fr | | À faire | À réinstaller |
| Test | `Test/ruffkingmusic.com` | ruffkingmusic.com | | À faire | À réinstaller |
| Test | autres dossiers sous `Test` | — | | Ignoré | Tout le reste du dossier Test est écarté |
| Wegom | `Wegom/Claye` | Téléthon | | À faire | À réinstaller |
| Wegom | `Wegom/Clio` | Clio | | Reporté | AF |
| Wegom | `Wegom/Dentsu` | Dentsu | Domotique | Reporté | Même famille que les projets Dentsu |
| Wegom | `Wegom/Euler` | Calendrier | | À faire | À réinstaller |
| Wegom | `Wegom/FAST EPIL` | — | | Ignoré | Administratif |
| Wegom | `Wegom/Mailer Seb` | — | | Ignoré | |
| Wegom | `Wegom/Sanier` | Sanier projet 1 | | Reporté | AF ; projet à retrouver, dossier actuel vide |
| Wegom | `Wegom/Sanier` | Sanier projet 2 | | Reporté | AF ; projet à retrouver, dossier actuel vide |
| Wegom | `Wegom/Sorbone` | — | Audiovisuel | Ignoré | Intervention audiovisuelle |
| Wegom | `Wegom/Wegom 2018-05-31 12-50-53` | — | | Ignoré | Sauvegarde datée |
| Wegom | absent de l'inventaire | WegAtt | VB6 | Reporté | Retrouver des captures d'écran pour l'archive |
| Wegom | `Wegom/election` | Mairie de Chennevières | | À faire | À réinstaller |
| Wegom | `Wegom/sql_monitor_wego` | — | | Ignoré | |
| Wegom | `Wegom/wegom` | — | | Ignoré | Application interne |
| Wegom | `Wegom/wegom app` | — | | Ignoré | Application interne |
| conseil | `conseil/aamac.local_20250825_070017` | Conseil municipal | PHP | À faire | À réinstaller |
| efficience | `efficience/cfes` | cfes | Netscape Server | Reporté | AF ; projet ancien à conserver |
| eridia | `eridia` | eridia | HTML statique | Archivé | En ligne sur https://hachette.eridia.archive.cowprod.net/ ; J1 réussi. 4 liens internes restent à vérifier dans les sauvegardes source : ART02_LISEDC_F.html, COL02_GESCOL_F.html, INT03_MAJINT_F.html, INT03_MAJINT.html |
| ms | `ms/Cadref` | Cadref | PHP | À faire | À réinstaller |
| ms | `ms/Duplicate Qr-Code` | Duplicate Qr-Code | Android | Reporté | |
| ms | `ms/Melons` | Melons | | Ignoré | Rien d'utile dans ce dossier ; site déjà réinstallé |
| ms | absent de l'inventaire | prod.bernardchiron.org | | À faire | À réinstaller |
| ms | `ms/Samsung` | Samsung — application | Android | Reporté | |
| ms | `ms/Samsung` | Samsung — back-office | PHP | À faire | À réinstaller |
| ms | `ms/Wifimage` | Wifimage | Android | Reporté | |
| ms | `ms/sanicard` | sanicard | Android | Reporté | |
| phyto | `phyto` | — | | Ignoré | |
| phyto | `www.phyto-terra.com` | — | | Ignoré | |

## Règles de triage

1. On traite les dossiers **un par un**.
2. La profondeur n'est pas imposée : selon le client, le projet peut être le dossier de niveau 1, 2 ou plus profond.
3. Un projet techniquement difficile ou actuellement inexploitable reste visible avec le statut **Reporté**.
4. **AF** signifie **à fouiller** ; ces éléments restent en **Reporté** tant qu'ils n'ont pas été examinés.
5. Les sauvegardes, doublons et répertoires techniques peuvent être marqués **Ignoré** sans être supprimés de l'inventaire.
6. Quand un projet est effectivement saisi sur le serveur d'archives, son statut passe à **Archivé**.
7. Les dossiers Synology `@eaDir` ne sont jamais des projets et sont exclus.
