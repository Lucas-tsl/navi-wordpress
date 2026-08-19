#!/usr/bin/env bash
# Déploie le plugin navi vers l'instance WordPress/WooCommerce de dev locale
# (docker-compose.yml de ce dépôt), puis vérifie que le site répond.
#
# Le plugin vit à la racine du dépôt (navi.php, includes/, assets/,
# languages/, uninstall.php) : on assemble d'abord un dossier propre via
# rsync + .distignore (exactement ce que fait le job "build" de la CI, pour
# tester le même contenu que ce qui serait réellement distribué), puis on le
# copie dans le conteneur — pas de dossier séparé côté dépôt comme côté
# PrestaShop. Dossier de destination nommé "saito-navi" (pas "navi") :
# c'est le slug WordPress.org réel (dérivé du nom du plugin "Saito Navi",
# "navi" seul n'étant pas disponible), qui doit correspondre au Text Domain
# (navi.php) pour que Plugin Check ne remonte pas de textdomain_mismatch.
#
# Usage : ./scripts/deploy-local.sh [--no-verify]
#   NAVI_DEPLOY_CONTAINER : nom du conteneur web (défaut navi_wp_web)
#   NAVI_DEPLOY_BASE_URL  : URL utilisée pour la vérification finale
#                            (défaut http://localhost:8082)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(dirname "$SCRIPT_DIR")"

CONTAINER="${NAVI_DEPLOY_CONTAINER:-navi_wp_web}"
BASE_URL="${NAVI_DEPLOY_BASE_URL:-http://localhost:8082}"
MODULE_DEST="/var/www/html/wp-content/plugins/saito-navi"

VERIFY=1
for arg in "$@"; do
    case "$arg" in
        --no-verify) VERIFY=0 ;;
        *)
            echo "Argument inconnu : $arg" >&2
            exit 1
            ;;
    esac
done

if ! docker exec "$CONTAINER" true 2>/dev/null; then
    echo "Erreur : conteneur '$CONTAINER' inaccessible (docker exec a échoué)" >&2
    exit 1
fi

BUILD_DIR="$(mktemp -d)"
trap 'rm -rf "$BUILD_DIR"' EXIT

echo "==> Assemblage du plugin (rsync + .distignore) dans $BUILD_DIR"
rsync -a --exclude-from="$REPO_ROOT/.distignore" "$REPO_ROOT/" "$BUILD_DIR/saito-navi/"

echo "==> Copie vers $CONTAINER:$MODULE_DEST"
docker exec "$CONTAINER" mkdir -p "$MODULE_DEST"
docker exec "$CONTAINER" sh -c "rm -rf $MODULE_DEST/*"
docker cp "$BUILD_DIR/saito-navi/." "$CONTAINER:$MODULE_DEST"

# Toujours www-data : docker cp copie avec l'UID de l'hôte, l'image
# WordPress officielle sert le site en www-data (comme sur presta_web).
echo "==> chown www-data:www-data sur $MODULE_DEST"
docker exec "$CONTAINER" chown -R www-data:www-data "$MODULE_DEST"

if [ "$VERIFY" -eq 1 ]; then
    echo "==> Vérification HTTP ($BASE_URL)"
    status=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL" || echo "000")
    if [ "$status" != "200" ] && [ "$status" != "302" ]; then
        echo "Attention : $BASE_URL a répondu $status — vérifier manuellement avant de continuer" >&2
        exit 1
    fi
    echo "OK : $BASE_URL répond $status"
fi

echo "==> Déploiement terminé"
