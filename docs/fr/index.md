# Plugin Events Manager — Documentation

## Présentation

Le plugin **Events Manager** pour GLPI permet de gérer des **événements informatiques** (alertes, incidents, avertissements, informations) indépendamment des tickets. Un événement suit son propre cycle de vie (Nouveau → Assigné → Fermé), peut être enrichi de commentaires, lié à des actifs GLPI, puis converti en ticket ou associé à des tickets existants. Des origines configurables (collecteur de mails, flux RSS, API REST) permettent l'alimentation automatique des événements.

- **Version** : 4.0.0
- **Compatibilité GLPI** : 11.x (< 12.0)
- **Licence** : GPLv3+
- **Auteurs** : Infotel, Xavier CAILLAUD

---

## Fonctionnalités

- **Gestion d'événements** : créer, qualifier et suivre des événements avec priorité, impact, type et délai de résolution
- **Cycle de vie** : trois statuts — Nouveau, Assigné, Fermé
- **Cinq types d'événements** : Indéfini, Information, Avertissement, Exception, Alerte (code couleur)
- **Actions rapides en liste** : s'assigner, créer un ticket, fermer l'événement depuis l'icône d'action
- **Association d'actifs** : lier tout type d'actif GLPI à un événement
- **Commentaires threaded** : discussion hiérarchique (réponses imbriquées) avec édition AJAX
- **Intégration tickets** : créer un ticket depuis un événement (héritage des champs), lier des tickets existants, voir les événements depuis la fiche ticket
- **Fermeture automatique** : option pour fermer l'événement automatiquement lors de la création du ticket
- **Origines** : qualifier l'origine d'un événement (collecteur de mails, flux RSS, API REST, autres)
- **Import e-mail** : tab sur les collecteurs de mails GLPI pour créer des événements via règles de mails
- **Import RSS** : tab sur les flux RSS GLPI, tâche cron d'import automatique
- **Base de connaissances** : association d'articles de la base de connaissances à un événement
- **Documents** : pièces jointes transmises au ticket lors de la création
- **Action massive** : transfert d'événements entre entités
- **Intégration MyDashboard** : widget d'alerte si le plugin MyDashboard est installé
- **Interface simplifiée** : accès aux événements dans le menu helpdesk (droit `plugin_eventsmanager`)

---

## Prérequis et installation

1. Télécharger l'archive depuis [GitHub Releases](https://github.com/InfotelGLPI/eventsmanager/releases).
2. Décompresser dans le répertoire `plugins/` de GLPI.
3. Se connecter à GLPI en tant qu'administrateur.
4. Aller dans **Configuration → Plugins** et activer **Events Manager**.

---

## Droits

Events Manager utilise un **droit unique** :

| Droit | Description |
|---|---|
| `plugin_eventsmanager` | Accès à toutes les fonctionnalités (lecture, création, modification, suppression, purge) |

Ce droit s'attribue dans **Administration → Profils**, onglet **Events Manager**. Les droits standards GLPI s'appliquent : `READ`, `CREATE`, `UPDATE`, `DELETE`, `PURGE`.

La configuration du plugin nécessite le droit GLPI `config` en écriture.

---

## Configuration globale

**Accès** : **Configuration → Plugins → Events Manager (Configurer)**

| Paramètre | Description |
|---|---|
| Fermeture automatique lors de la création d'un ticket | Si activé, l'événement passe automatiquement à l'état Fermé dès qu'un ticket est créé depuis cet événement |

---

## Événements

**Accès** : **Helpdesk → Events** (interface simplifiée) ou menu principal (interface centrale)

### Champs d'un événement

| Champ | Description |
|---|---|
| Nom | Titre de l'événement |
| Impact | Impact (identique aux tickets GLPI : très bas → très haut) |
| Priorité | Priorité (identique aux tickets GLPI) |
| Type d'événement | Indéfini / Information / Avertissement / Exception / Alerte |
| Origine | Origine qualifiée (collecteur de mails, flux RSS, API REST, autres) |
| Utilisateur assigné | Technicien responsable de l'événement |
| Délai de résolution | Date/heure limite de traitement |
| Statut | Nouveau / Assigné / Fermé |
| Description | Contenu riche (éditeur HTML) |
| Élément associé | Actif GLPI lié à l'événement |

### Statuts

| Statut | Constante | Description |
|---|---|---|
| Nouveau | `NEW_STATE = 1` | Événement créé, non pris en charge |
| Assigné | `ASSIGNED_STATE = 2` | Technicien désigné (automatique si un utilisateur est assigné à la création) |
| Fermé | `CLOSED_STATE = 3` | Événement traité et clôturé |

### Types d'événements et couleurs

| Type | Couleur de fond |
|---|---|
| Indéfini | Gris clair |
| Information | Vert clair |
| Avertissement | Bleu (CornflowerBlue) |
| Exception | Orange |
| Alerte | Rouge |

### Actions rapides

Depuis la liste des événements et depuis la fiche (statut non Fermé) :

| Icône | Action |
|---|---|
| Icône utilisateur+ | S'assigner à l'événement (AJAX) |
| Icône cloche | Créer un ticket depuis l'événement |
| Icône archive | Fermer l'événement (AJAX) |

---

## Origines

**Accès** : Dropdown dans la fiche événement

Une **origine** qualifie la source de l'événement. Elle combine un **type de source** et un **élément** de ce type :

| Type | Élément associé |
|---|---|
| Collecteur de mails (`Collector`) | Collecteur de mails GLPI |
| Flux RSS (`RSS`) | Flux RSS GLPI |
| API REST (`Api`) | Aucun élément |
| Autres (`Others`) | Aucun élément |

Chaque origine peut également être associée à un **type de demande** GLPI, qui sera repris lors de la création automatique d'un ticket depuis cet événement.

---

## Intégration tickets

### Créer un ticket depuis un événement

Depuis la fiche d'un événement non fermé, l'onglet **Tickets** propose :
- **Créer un nouveau ticket** : pré-remplit le ticket avec le nom, la description, la priorité, l'impact, le délai de résolution et l'entité de l'événement. Les documents et les actifs associés à l'événement sont également transférés au ticket.
- **Lier un ticket existant** : associer un ticket déjà existant à l'événement.

Si la **fermeture automatique** est activée dans la configuration, l'événement passe à l'état Fermé dès que le ticket est créé.

### Voir les événements depuis un ticket

Un onglet **Événements liés** apparaît sur la fiche de chaque ticket GLPI, listant les événements associés avec leur statut, priorité, type et actifs liés.

---

## Import e-mail

**Accès** : **Configuration → Collecteurs de mails → (fiche d'un collecteur) → onglet Events Manager**

Permet de configurer les valeurs par défaut appliquées lors de la création automatique d'un événement via les règles de mails GLPI :

| Paramètre | Description |
|---|---|
| Impact par défaut | Impact appliqué à l'événement créé |
| Priorité par défaut | Priorité appliquée |
| Type d'événement par défaut | Type d'événement appliqué |

La création effective est déclenchée par une **règle de collecteur de mails** GLPI utilisant l'action **"Affecter une entité pour créer un événement"** fournie par le plugin.

---

## Import RSS

**Accès** : **Outils → Flux RSS → (fiche d'un flux) → onglet Events Manager**

Permet d'activer l'import automatique des articles d'un flux RSS GLPI en événements :

| Paramètre | Description |
|---|---|
| Utiliser ce flux pour créer des événements | Oui / Non |
| Entité de destination | Entité dans laquelle les événements sont créés |
| Impact par défaut | Impact appliqué |
| Priorité par défaut | Priorité appliquée |
| Type d'événement par défaut | Type d'événement appliqué |

La tâche cron **`RssImport`** (Events Manager) s'exécute périodiquement et crée un événement pour chaque nouvel article du flux (depuis le dernier article importé).

---

## Commentaires

Depuis la fiche d'un événement, l'onglet **Commentaires** affiche une discussion en fil hiérarchique :
- Ajouter un commentaire
- Répondre à un commentaire (imbrication)
- Modifier son propre commentaire (AJAX)

---

## Intégration MyDashboard

Si le plugin **MyDashboard** est installé, un widget de type **Alerte** peut être ajouté au dashboard depuis la fiche d'un événement (onglet dédié).

---

## Structure des tables

| Table | Description |
|---|---|
| `glpi_plugin_eventsmanager_events` | Événements (nom, statut, priorité, impact, type, origine, dates) |
| `glpi_plugin_eventsmanager_origins` | Origines configurées (type de source, élément associé, type de demande) |
| `glpi_plugin_eventsmanager_events_items` | Actifs liés à un événement |
| `glpi_plugin_eventsmanager_tickets` | Liens entre événements et tickets GLPI |
| `glpi_plugin_eventsmanager_events_comments` | Commentaires (hiérarchiques) d'un événement |
| `glpi_plugin_eventsmanager_mailimports` | Configuration import e-mail par collecteur |
| `glpi_plugin_eventsmanager_rssimports` | Configuration import RSS par flux |
| `glpi_plugin_eventsmanager_configs` | Configuration globale du plugin |

---

## Désinstallation

Dans **Configuration → Plugins**, désactiver puis désinstaller **Events Manager**. Toutes les tables `glpi_plugin_eventsmanager_*` sont supprimées ainsi que les droits de profil et les tâches cron associés.

---

## Liens utiles

- [Dépôt GitHub](https://github.com/InfotelGLPI/eventsmanager)
- [Signaler un bug](https://github.com/InfotelGLPI/eventsmanager/issues)
- [Contribuer à la traduction](https://explore.transifex.com/infotelGLPI/GLPI_eventsmanager/)
- [Blog Infotel GLPI](https://blogglpi.infotel.com)
