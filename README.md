# Nimba SMS for WordPress

Extension WordPress officielle de [Nimba SMS](https://www.nimbasms.com), la plateforme de communication professionnelle des entreprises (SMS, WhatsApp, e-mail — et d'autres canaux à venir). Cette extension couvre les canaux **SMS** et **WhatsApp** (templates approuvés par Meta) : envoyez des messages avec le nom de votre entreprise comme expéditeur, directement depuis WordPress.

Support : [support@nimbasms.com](mailto:support@nimbasms.com)

[![WordPress Plugin](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/plugins/nimbasms/)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](LICENSE)

## Fonctionnalités

- **WooCommerce** — SMS et/ou WhatsApp au client par statut de commande (les deux canaux sont cumulables ; repli SMS automatique si WhatsApp seul échoue), modèles avec variables `{order_id}`, `{total}`…, et SMS à l'administrateur à chaque nouvelle commande
- **WhatsApp** — envoi via templates WhatsApp Business approuvés par Meta, variables dynamiques passées dans l'ordre du template (en canal WhatsApp, seul le template est envoyé)
- **Notifications WordPress** — nouvelle inscription, nouveau commentaire
- **Envoi manuel** de SMS depuis l'administration
- **Journal des envois** avec statuts
- **Solde du compte** affiché en temps réel
- **API développeur** — `nimbasms_send( $to, $message )` + hooks

## Installation

Depuis l'admin WordPress : **Extensions → Ajouter → rechercher « Nimba SMS » → Installer**.

Ou manuellement :

```bash
cd wp-content/plugins
git clone https://github.com/nimbasms/nimbasms-wordpress.git nimbasms
```

Puis activez l'extension et renseignez vos identifiants (menu **Nimba SMS**). Identifiants disponibles sur [la page Clés API de votre compte](https://www.nimbasms.com/app/api-keys), documentation sur [developers.nimbasms.com](https://developers.nimbasms.com).

## Pour les développeurs

```php
// SMS simple
nimbasms_send( '624000000', 'Bonjour depuis WordPress !' );

// SMS multiple avec nom d'expéditeur spécifique
nimbasms_send( array( '624000000', '625000000' ), 'Message', 'MASOCIETE' );

// WhatsApp via template approuvé — variables dans l'ordre du template
nimbasms_send_whatsapp( '624000000', 'commande_confirmee', array( 'Fodé', '45 000 GNF' ) );
```

Hooks disponibles :

| Hook | Type | Description |
|---|---|---|
| `nimbasms_send_payload` | filtre | Modifier la charge utile avant envoi |
| `nimbasms_after_send` | action | Après chaque tentative d'envoi |
| `nimbasms_wc_templates` | filtre | Statuts WooCommerce déclencheurs et leurs modèles |
| `nimbasms_user_phone` | filtre | Résolution du numéro d'un utilisateur |
| `nimbasms_normalize_number` | filtre | Normalisation des numéros |

## Développement

```bash
composer install        # outils de dev (PHPCS + WordPress Coding Standards)
composer run lint       # vérification des standards
```

Le déploiement vers le SVN wordpress.org est automatisé par GitHub Actions à chaque release (tag `vX.Y.Z`).

## Contribuer

Les pull requests sont bienvenues. Consultez les [guidelines de l'organisation](https://github.com/nimbasms/.github).

## Licence

[GPLv2 or later](LICENSE)
