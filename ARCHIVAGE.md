# Suivi d'archivage des projets

Objectif : archiver au moins un projet par jour dans archiveCowprod.

## Statuts

- **À qualifier** : il faut décider si le dossier correspond à un projet archivable et à quelle granularité.
- **À faire** : projet faisable et prêt à être archivé.
- **Reporté** : projet identifié mais non faisable pour le moment (ASP, CD-ROM, dépendance technique, données manquantes, etc.).
- **Archivé** : projet traité dans archiveCowprod.
- **Ignoré** : sauvegarde, doublon, dossier technique ou élément qui ne doit pas devenir un projet.

## File d'attente

| Groupe / client | Dossier source | Projet candidat | Technologies | Statut | Notes |
|---|---|---|---|---|---|
| A la folie | `A la folie/2019_bandOrganizer` | 2019_bandOrganizer | | À qualifier | |
| A la folie | `A la folie/20230504_BO` | 20230504_BO | | À qualifier | |
| A la folie | `A la folie/BO` | BO | | À qualifier | |
| A la folie | `A la folie/BOBAKCUP` | BOBAKCUP | | À qualifier | |
| A la folie | `A la folie/DEL_affiche.legroupealafolie.org` | affiche.legroupealafolie.org | | À qualifier | Préfixe DEL |
| A la folie | `A la folie/DEL_demo.legroupealafolie.org` | demo.legroupealafolie.org | | À qualifier | Préfixe DEL |
| A la folie | `A la folie/Telecaster` | Telecaster | | À qualifier | |
| A la folie | `A la folie/bandorganizer` | bandorganizer | | À qualifier | |
| A la folie | `A la folie/bandorganizer_bck` | bandorganizer_bck | | À qualifier | Probable sauvegarde |
| A la folie | `A la folie/bandorganizer_client_old` | bandorganizer_client_old | | À qualifier | Probable ancienne version |
| A la folie | `A la folie/bandorganizer_del` | bandorganizer_del | | À qualifier | Probable ancienne version |
| A la folie | `A la folie/conductor` | conductor | | À qualifier | |
| A la folie | `A la folie/tempBO` | tempBO | | À qualifier | Probable temporaire |
| Arte Factory | `Arte Factory` | À découper | | À qualifier | Sous-dossiers visibles : Bezons Coeur de ville, Teaser Montparnasse |
| Bobigny | `Bobigny` | À découper | | À qualifier | Sous-dossier visible : Bobigny |
| Christian | `Christian` | À découper | | À qualifier | CM - Musique, Famille, Pour l'histoire, pourlhistoire |
| Delmont Imaging | `Delmont Imaging` | À découper | | À qualifier | Plusieurs versions / projets techniques |
| Dentsu | `Dentsu` | À découper | | À qualifier | Salle du board, Temperature |
| Dossier404 | `Dossier404` | À découper | | À qualifier | Ciel, contact |
| F.O. | `F.O.` | À découper | | À qualifier | FNEM, salle de réunion, baie, intranet |
| LAPSA | `LAPSA` | À découper | | À qualifier | Plusieurs versions, backoffice/webapp |
| Listee | `Listee` | À découper | | À qualifier | Plusieurs générations / sauvegardes |
| Paix Liturgique | `Paix Liturgique` | À découper | | À qualifier | Sites, Textus, fichiers historiques |
| Perso | `Perso` | À découper | | À qualifier | Ensemble hétérogène |
| Piaraly | `Piaraly` | À découper | | À qualifier | Nombreux projets / documents |
| Test | `Test` | À découper | | À qualifier | Nombreux POC ; granularité à décider |
| Wegom | `Wegom` | À découper | | À qualifier | Plusieurs clients / projets |
| conseil | `conseil` | À découper | | À qualifier | aamac.local_20250825_070017 |
| efficience | `efficience` | À découper | | À qualifier | cfes |
| eridia | `eridia` | À découper | | À qualifier | cowprod.zip, prod, rea |
| ms | `ms` | À découper | | À qualifier | Cadref, Duplicate Qr-Code, Melons, Samsung, Wifimage, sanicard |
| phyto | `phyto` | À découper | | À qualifier | www.phyto-terra.com |

## Règles de triage

1. On traite les dossiers **un par un**.
2. La profondeur n'est pas imposée : selon le client, le projet peut être le dossier de niveau 1, 2 ou plus profond.
3. Un projet techniquement difficile ou actuellement inexploitable reste visible avec le statut **Reporté**.
4. Les sauvegardes, doublons et répertoires techniques peuvent être marqués **Ignoré** sans être supprimés de l'inventaire.
5. Quand un projet est effectivement saisi sur le serveur d'archives, son statut passe à **Archivé**.
6. Les dossiers Synology `@eaDir` ne sont jamais des projets et sont exclus.
