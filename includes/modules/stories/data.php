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

const NAVI_STORY_LIMIT = 4;

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
 * Vignette YouTube par défaut d'une story sans prévisualisation
 * personnalisée. maxresdefault.jpg n'existe que pour les vidéos avec un
 * master HD grand écran — quasiment jamais le cas des Shorts (format
 * vertical), qui n'ont souvent qu'un simple hqdefault.jpg. Un
 * wp_remote_head() vérifie sa disponibilité avant d'y recourir plutôt que
 * de stocker une URL d'image cassée dans le postmeta.
 */
function navi_youtube_thumbnail_url( $youtube_id ) {
    $maxres   = 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg';
    $response = wp_remote_head( $maxres, array( 'timeout' => 3 ) );
    if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
        return $maxres;
    }

    return 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg';
}

/**
 * Dossier historique des prévisualisations MP4 uploadées avant l'ajout du
 * sélecteur médiathèque (wp.media) — celui-ci passe désormais par le flux
 * d'upload standard de WordPress (dossier daté de la médiathèque, vraie
 * entrée wp_insert_attachment()), donc plus rien n'écrit ici. Conservé
 * uniquement pour que navi_stories_uninstall_cleanup() efface les fichiers
 * restants des installations antérieures à ce changement.
 */
function navi_stories_upload_dir() {
    $upload_dir = wp_upload_dir();
    return trailingslashit( $upload_dir['basedir'] ) . 'navi-stories/';
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

    $slots = array();

    for ( $index = 1; $index <= NAVI_STORY_LIMIT; $index++ ) {
        $youtube = navi_extract_youtube_id(
            isset( $_POST[ 'navi_story_youtube_' . $index ] ) ? wp_unslash( $_POST[ 'navi_story_youtube_' . $index ] ) : '' // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce vérifié par l'appelant ; navi_extract_youtube_id() ne renvoie qu'un identifiant YouTube (regex, 11 caractères alphanumériques) ou une chaîne vide.
        );
        if ( '' === $youtube ) {
            continue; // Emplacement vide : pas de story à cet index.
        }

        // URL soit saisie à la main, soit déposée par le sélecteur médiathèque
        // (voir admin-product-tab.php) qui remplit ce même champ en JS.
        $preview = isset( $_POST[ 'navi_story_preview_' . $index ] ) ? esc_url_raw( wp_unslash( $_POST[ 'navi_story_preview_' . $index ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié par l'appelant ; valeur passée à esc_url_raw().
        if ( '' === $preview ) {
            $preview = navi_youtube_thumbnail_url( $youtube );
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
