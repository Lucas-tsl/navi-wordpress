<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Couche données des stories : stockage en post meta (voir la note dans le
 * plan — postmeta est le mécanisme idiomatique WordPress pour une donnée
 * rattachée à un produit, contrairement à la table SQL dédiée utilisée côté
 * Navi PrestaShop, qui se justifiait par le scoping multiboutique par
 * id_shop, absent ici). Un seul meta `_navi_stories` (tableau sérialisé de
 * jusqu'à NAVI_STORY_LIMIT entrées), plutôt que 12 clés séparées.
 */

const NAVI_STORY_LIMIT      = 4;
const NAVI_STORY_MAX_BYTES  = 20971520; // 20 Mo, même plafond que côté PrestaShop.

/**
 * Stories du produit, toujours NAVI_STORY_LIMIT entrées (index 1 à 4, les
 * emplacements vides ont youtube/preview/label à ''), jamais moins — évite
 * à l'appelant (onglet admin, rendu front) de vérifier l'existence de
 * chaque index à chaque fois.
 */
function navi_stories_get( $product_id ) {
    $stored = get_post_meta( $product_id, '_navi_stories', true );
    if ( ! is_array( $stored ) ) {
        $stored = array();
    }

    $slots = array();
    for ( $index = 1; $index <= NAVI_STORY_LIMIT; $index++ ) {
        $entry           = isset( $stored[ $index ] ) && is_array( $stored[ $index ] ) ? $stored[ $index ] : array();
        $slots[ $index ] = array(
            'youtube' => isset( $entry['youtube'] ) ? (string) $entry['youtube'] : '',
            'preview' => isset( $entry['preview'] ) ? (string) $entry['preview'] : '',
            'label'   => isset( $entry['label'] ) ? (string) $entry['label'] : '',
        );
    }

    return $slots;
}

// Stories réellement configurées (youtube non vide) seulement, dans l'ordre
// des index — ce que consomme le rendu front (public-display.php),
// contrairement à navi_stories_get() qui renvoie toujours les 4 emplacements
// (utilisé par l'onglet admin, qui doit afficher les emplacements vides).
function navi_stories_get_configured( $product_id ) {
    return array_values(
        array_filter(
            navi_stories_get( $product_id ),
            function ( $story ) {
                return '' !== $story['youtube'];
            }
        )
    );
}

/**
 * Accepte une URL YouTube complète (watch?v=, youtu.be/, /shorts/) ou un
 * identifiant brut de 11 caractères déjà saisi tel quel — port verbatim de
 * la version PrestaShop (fonction pure, aucune dépendance de framework).
 */
function navi_extract_youtube_id( $input ) {
    $input = trim( (string) $input );
    if ( '' === $input ) {
        return '';
    }

    if ( preg_match( '#(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $input, $m ) ) {
        return $m[1];
    }

    if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $input ) ) {
        return $input;
    }

    return '';
}

/**
 * Validation centralisée (extension + MIME + taille) — même logique que
 * NaviStoryManager::validateMp4Upload() côté PrestaShop. Retourne un
 * message d'erreur, ou '' si le fichier est accepté.
 */
function navi_validate_mp4_upload( array $file ) {
    if ( ! isset( $file['error'] ) || UPLOAD_ERR_OK !== $file['error'] ) {
        return __( 'Erreur lors du transfert du fichier.', 'navi' );
    }

    if ( $file['size'] > NAVI_STORY_MAX_BYTES ) {
        return sprintf(
            /* translators: %d: taille maximale en Mo */
            __( 'Le fichier dépasse la taille maximale autorisée (%d Mo).', 'navi' ),
            NAVI_STORY_MAX_BYTES / 1048576
        );
    }

    $extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
    if ( 'mp4' !== $extension ) {
        return __( 'Seuls les fichiers .mp4 sont acceptés.', 'navi' );
    }

    if ( function_exists( 'finfo_open' ) ) {
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime  = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );
        if ( 'video/mp4' !== $mime ) {
            return __( 'Le fichier ne semble pas être une vidéo MP4 valide.', 'navi' );
        }
    }

    return '';
}

/**
 * Dossier d'upload des prévisualisations MP4 — wp_upload_dir(), PAS un
 * sous-dossier du plugin (voir la note du plan : écrire des fichiers
 * uploadés par l'utilisateur dans le dossier du plugin est déconseillé par
 * les revues WordPress.org, dossier parfois non inscriptible selon
 * l'hébergeur, écrasé à chaque mise à jour du plugin).
 */
function navi_stories_upload_dir() {
    $upload_dir = wp_upload_dir();
    return trailingslashit( $upload_dir['basedir'] ) . 'navi-stories/';
}

function navi_stories_upload_url() {
    $upload_dir = wp_upload_dir();
    return trailingslashit( $upload_dir['baseurl'] ) . 'navi-stories/';
}

function navi_stories_ensure_upload_dir() {
    $dir = navi_stories_upload_dir();
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }
    return is_dir( $dir );
}

/**
 * Déplace un upload validé vers le dossier dédié et retourne son URL
 * publique, ou null si aucun fichier valide n'a été soumis pour cet index
 * (absence de fichier n'est pas une erreur : l'admin peut avoir laissé ce
 * champ vide volontairement).
 */
function navi_stories_handle_uploaded_preview( $index, &$errors ) {
    $field = 'navi_story_preview_file_' . (int) $index;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- nonce vérifié par l'appelant (navi_stories_process_product_meta, admin-product-tab.php) avant navi_stories_save() ; UPLOAD_ERR_* est un entier fourni par PHP, pas une entrée utilisateur.
    if ( ! isset( $_FILES[ $field ] ) || UPLOAD_ERR_NO_FILE === $_FILES[ $field ]['error'] ) {
        return null;
    }

    $file  = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce vérifié par l'appelant ; contenu validé ci-dessous (extension/MIME/taille), pas affiché tel quel.
    $error = navi_validate_mp4_upload( $file );
    if ( '' !== $error ) {
        $errors[] = sprintf( '#%d — %s', $index, $error );
        return null;
    }

    if ( ! navi_stories_ensure_upload_dir() ) {
        $errors[] = sprintf( '#%d — %s', $index, __( "Échec de la création du dossier d'upload.", 'navi' ) );
        return null;
    }

    $filename    = 'story_' . (int) $index . '_' . time() . '_' . wp_generate_password( 8, false ) . '.mp4';
    $destination = navi_stories_upload_dir() . $filename;

    if ( ! move_uploaded_file( $file['tmp_name'], $destination ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_move_uploaded_file -- upload validé (extension/MIME/taille) ; wp_handle_upload attend un tableau $_FILES complet et déplacerait vers la médiathèque, pas le sous-dossier dédié voulu ici.
        $errors[] = sprintf( '#%d — %s', $index, __( "Échec de l'enregistrement du fichier.", 'navi' ) );
        return null;
    }

    return navi_stories_upload_url() . $filename;
}

/**
 * Point d'entrée unique de sauvegarde, appelé depuis
 * woocommerce_process_product_meta (voir admin-product-tab.php) — jamais
 * depuis un contrôleur front public.
 */
function navi_stories_save( $product_id ) {
    $product_id = (int) $product_id;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié par l'appelant (navi_stories_process_product_meta, admin-product-tab.php) avant navi_stories_save().
    if ( ! $product_id || ! isset( $_POST['navi_story_submitted'] ) ) {
        return;
    }

    $errors = array();
    $slots  = array();

    for ( $index = 1; $index <= NAVI_STORY_LIMIT; $index++ ) {
        $youtube = navi_extract_youtube_id(
            isset( $_POST[ 'navi_story_youtube_' . $index ] ) ? wp_unslash( $_POST[ 'navi_story_youtube_' . $index ] ) : '' // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce vérifié par l'appelant ; navi_extract_youtube_id() ne renvoie qu'un identifiant YouTube (regex, 11 caractères alphanumériques) ou une chaîne vide.
        );
        if ( '' === $youtube ) {
            continue; // Emplacement vide : pas de story à cet index.
        }

        $uploaded_url = navi_stories_handle_uploaded_preview( $index, $errors );
        $preview = $uploaded_url
            ? $uploaded_url
            : ( isset( $_POST[ 'navi_story_preview_' . $index ] ) ? esc_url_raw( wp_unslash( $_POST[ 'navi_story_preview_' . $index ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié par l'appelant ; valeur passée à esc_url_raw().
        if ( '' === $preview ) {
            $preview = 'https://img.youtube.com/vi/' . $youtube . '/maxresdefault.jpg';
        }

        $slots[ $index ] = array(
            'youtube' => sanitize_text_field( $youtube ),
            'preview' => esc_url_raw( $preview ),
            'label'   => isset( $_POST[ 'navi_story_label_' . $index ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'navi_story_label_' . $index ] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié par l'appelant ; valeur passée à sanitize_text_field().
        );
    }

    if ( empty( $slots ) ) {
        delete_post_meta( $product_id, '_navi_stories' );
    } else {
        update_post_meta( $product_id, '_navi_stories', $slots );
    }

    if ( ! empty( $errors ) ) {
        // WooCommerce affiche ces notices admin au rechargement de la page
        // produit (WC_Admin_Notices utilise aussi cette fonction en
        // interne) — cohérent avec le reste de l'admin WooCommerce.
        if ( function_exists( 'wc_add_notice' ) ) {
            wc_add_notice(
                __( 'Stories Navi : certains fichiers ont été ignorés.', 'navi' ) . ' ' . implode( ' ', $errors ),
                'error'
            );
        }
    }
}

// Nettoyage à la désinstallation (voir uninstall.php) : supprime les
// fichiers uploadés — pas de table à supprimer (le postmeta part avec le
// produit/le site).
function navi_stories_uninstall_cleanup() {
    $dir = navi_stories_upload_dir();
    if ( ! is_dir( $dir ) ) {
        return;
    }
    foreach ( glob( $dir . '*.mp4' ) as $file ) {
        wp_delete_file( $file );
    }
}
