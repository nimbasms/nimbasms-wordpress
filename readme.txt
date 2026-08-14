=== Nimba SMS ===
Contributors: nimbasms
Tags: sms, whatsapp, woocommerce, notifications, guinea
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connectez WordPress à Nimba SMS, la plateforme de communication professionnelle des entreprises : notifications WooCommerce, alertes et SMS.

== Description ==

**Nimba SMS** connecte votre site WordPress à [Nimba SMS](https://www.nimbasms.com), la plateforme de communication professionnelle des entreprises, qui prend en charge le SMS, WhatsApp et l'e-mail (et d'autres canaux à venir). Cette extension couvre les canaux SMS et WhatsApp : envoyez des SMS avec le nom de votre entreprise comme expéditeur, et des messages WhatsApp via vos templates approuvés par Meta, directement depuis WordPress. Les autres canaux seront ajoutés dans les prochaines versions.

= Fonctionnalités =

* **WooCommerce** : pour chaque statut de commande (confirmée, terminée, annulée), activez le SMS, WhatsApp, ou les deux, avec modèles personnalisables. Si seul WhatsApp est activé et que l'envoi échoue, repli automatique en SMS. SMS à l'administrateur à chaque nouvelle commande.
* **WhatsApp** : envoi via vos templates WhatsApp Business approuvés par Meta (créés depuis votre dashboard Nimba SMS), avec variables dynamiques.
* **Notifications WordPress** : SMS à l'administrateur lors d'une nouvelle inscription ou d'un nouveau commentaire.
* **Envoi manuel** : envoyez un SMS à un ou plusieurs numéros directement depuis l'administration.
* **Journal des envois** : historique des messages envoyés avec leur statut, mis à jour en temps réel via le webhook de livraison Nimba SMS (URL fournie dans les réglages, à copier dans le champ « URL Webhook » de la page https://www.nimbasms.com/app/api-keys).
* **Solde en temps réel** : votre crédit SMS affiché dans les réglages.
* **Pour les développeurs** : fonctions `nimbasms_send( $to, $message )` et `nimbasms_send_whatsapp( $to, $template, $variables )` et hooks (`nimbasms_send_payload`, `nimbasms_after_send`, `nimbasms_wc_templates`…) pour intégrer le SMS dans vos propres extensions.

= Prérequis =

Un compte [Nimba SMS](https://www.nimbasms.com) avec un nom d'expéditeur approuvé. Vos identifiants API (SERVICE ID et SECRET TOKEN) sont disponibles sur [la page Clés API de votre compte](https://www.nimbasms.com/app/api-keys). Documentation de l'API : [developers.nimbasms.com](https://developers.nimbasms.com).

= Service externe =

Ce plugin communique avec l'API Nimba SMS (https://api.nimbasms.com) pour envoyer les SMS et récupérer le solde et les noms d'expéditeur de votre compte. Les données transmises sont : vos identifiants API, les numéros de téléphone destinataires et le contenu des messages. En sens inverse, si vous configurez le webhook de livraison, les serveurs de Nimba SMS appellent une URL de votre site (endpoint REST `nimbasms/v1/webhook`, protégé par un jeton secret) pour transmettre les changements de statut de vos messages. Voir les [conditions générales d'utilisation de Nimba SMS](https://www.nimbasms.com/conditions-generales-d-utilisation).

== Installation ==

1. Installez le plugin depuis le répertoire des extensions WordPress, ou téléversez le dossier `nimbasms` dans `/wp-content/plugins/`.
2. Activez l'extension.
3. Rendez-vous dans **Nimba SMS** dans le menu d'administration.
4. Renseignez votre SERVICE ID et votre SECRET TOKEN, choisissez votre nom d'expéditeur, enregistrez.
5. Activez les notifications souhaitées.

== Frequently Asked Questions ==

= Où trouver mes identifiants API ? =

Sur la page Clés API de votre compte : https://www.nimbasms.com/app/api-keys. Documentation complète sur developers.nimbasms.com.

= Le plugin fonctionne-t-il sans WooCommerce ? =

Oui. Les fonctions WooCommerce ne s'affichent que si WooCommerce est actif.

= Puis-je envoyer des SMS depuis mon propre code ? =

Oui : `nimbasms_send( '624000000', 'Mon message' );` — des filtres et actions sont disponibles pour personnaliser les envois.

= L'extension gère-t-elle WhatsApp ? =

Oui. Activez le canal WhatsApp dans les réglages, puis renseignez le nom d'un template approuvé par Meta (les templates se créent depuis votre dashboard Nimba SMS). L'e-mail et d'autres canaux arriveront dans les prochaines versions.

= Quels pays sont couverts ? =

Consultez la couverture réseau sur www.nimbasms.com.

== Screenshots ==

1. Réglages : identifiants API, solde et nom d'expéditeur.
2. Notifications WooCommerce avec modèles personnalisables.
3. Envoi manuel de SMS depuis l'administration.
4. Journal des envois.

== Changelog ==

= 1.0.0 =
* Version initiale : canaux SMS et WhatsApp (templates Meta), intégration WooCommerce avec repli SMS, notifications WordPress, envoi manuel, journal des envois, fonctions développeur.

== Upgrade Notice ==

= 1.0.0 =
Version initiale.
