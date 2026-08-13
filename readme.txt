=== Nimba SMS ===
Contributors: nimbasms
Tags: sms, woocommerce, notifications, guinea, otp
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connectez WordPress à Nimba SMS, la plateforme de communication professionnelle des entreprises : notifications WooCommerce, alertes et SMS.

== Description ==

**Nimba SMS** connecte votre site WordPress à [Nimba SMS](https://www.nimbasms.com), la plateforme de communication professionnelle des entreprises, qui prend en charge le SMS, WhatsApp et l'e-mail (et d'autres canaux à venir). Cette extension couvre le canal SMS : envoyez des SMS avec le nom de votre entreprise comme expéditeur, directement depuis WordPress. Les autres canaux seront ajoutés dans les prochaines versions.

= Fonctionnalités =

* **WooCommerce** : SMS automatique au client aux changements de statut de commande (confirmée, terminée, annulée) avec modèles personnalisables, et SMS à l'administrateur à chaque nouvelle commande.
* **Notifications WordPress** : SMS à l'administrateur lors d'une nouvelle inscription ou d'un nouveau commentaire.
* **Envoi manuel** : envoyez un SMS à un ou plusieurs numéros directement depuis l'administration.
* **Journal des envois** : historique des SMS envoyés avec leur statut.
* **Solde en temps réel** : votre crédit SMS affiché dans les réglages.
* **Pour les développeurs** : fonction `nimbasms_send( $to, $message )` et hooks (`nimbasms_send_payload`, `nimbasms_after_send`, `nimbasms_wc_templates`…) pour intégrer le SMS dans vos propres extensions.

= Prérequis =

Un compte [Nimba SMS](https://www.nimbasms.com) avec un nom d'expéditeur approuvé. Vos identifiants API (SERVICE ID et SECRET TOKEN) sont disponibles dans la rubrique Développeurs de votre compte. Documentation : [developers.nimbasms.com](https://developers.nimbasms.com).

= Service externe =

Ce plugin communique avec l'API Nimba SMS (https://api.nimbasms.com) pour envoyer les SMS et récupérer le solde et les noms d'expéditeur de votre compte. Les données transmises sont : vos identifiants API, les numéros de téléphone destinataires et le contenu des messages. Voir les [conditions d'utilisation](https://www.nimbasms.com/terms/) et la [politique de confidentialité](https://www.nimbasms.com/privacy/) de Nimba SMS.

== Installation ==

1. Installez le plugin depuis le répertoire des extensions WordPress, ou téléversez le dossier `nimbasms` dans `/wp-content/plugins/`.
2. Activez l'extension.
3. Rendez-vous dans **Nimba SMS** dans le menu d'administration.
4. Renseignez votre SERVICE ID et votre SECRET TOKEN, choisissez votre nom d'expéditeur, enregistrez.
5. Activez les notifications souhaitées.

== Frequently Asked Questions ==

= Où trouver mes identifiants API ? =

Dans votre compte Nimba SMS, rubrique Développeurs. Documentation complète sur developers.nimbasms.com.

= Le plugin fonctionne-t-il sans WooCommerce ? =

Oui. Les fonctions WooCommerce ne s'affichent que si WooCommerce est actif.

= Puis-je envoyer des SMS depuis mon propre code ? =

Oui : `nimbasms_send( '624000000', 'Mon message' );` — des filtres et actions sont disponibles pour personnaliser les envois.

= L'extension gère-t-elle WhatsApp et l'e-mail ? =

La plateforme Nimba SMS les prend en charge ; cette extension couvre le SMS aujourd'hui, les autres canaux arriveront dans les prochaines versions.

= Quels pays sont couverts ? =

Consultez la couverture réseau sur www.nimbasms.com.

== Screenshots ==

1. Réglages : identifiants API, solde et nom d'expéditeur.
2. Notifications WooCommerce avec modèles personnalisables.
3. Envoi manuel de SMS depuis l'administration.
4. Journal des envois.

== Changelog ==

= 1.0.0 =
* Version initiale : intégration WooCommerce, notifications WordPress, envoi manuel, journal des envois, fonctions développeur.

== Upgrade Notice ==

= 1.0.0 =
Version initiale.
