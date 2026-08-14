# Activer la CI et le déploiement wordpress.org

GitHub bloque l'ajout de workflows via un token sans la permission « Workflows ».
Pour activer : renommer ce dossier `workflows-a-activer` en `workflows`
(via l'interface web GitHub ou en local : `git mv .github/workflows-a-activer .github/workflows`).

- `ci.yml` — lint PHP 7.4→8.3 + PHPCS (WordPress Coding Standards) à chaque push/PR
- `deploy-wordpress-org.yml` — déploiement SVN wordpress.org à chaque release publiée
  (nécessite les secrets `WPORG_SVN_USERNAME` et `WPORG_SVN_PASSWORD`)
