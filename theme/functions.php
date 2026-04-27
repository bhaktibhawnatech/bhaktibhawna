<?php
/**
 * Bhakti Bhawna — child theme functions
 * Mobile-first, pure-PHP templates on Hello Elementor parent.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BB_VER', '2.12.0' );
define( 'BB_DIR', get_stylesheet_directory() );
define( 'BB_URI', get_stylesheet_directory_uri() );

/* Load tool classes & helpers */
require_once BB_DIR . '/tools/class-prokerala-api.php';
require_once BB_DIR . '/tools/class-bb-astro.php';
require_once BB_DIR . '/tools/helpers.php';

/* -----------------------------------------------------------------------
 * Theme setup
 * --------------------------------------------------------------------- */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'script', 'style' ) );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'bhaktibhawna' ),
        'footer'  => __( 'Footer Menu',  'bhaktibhawna' ),
    ) );
}, 20 );

/* -----------------------------------------------------------------------
 * Enqueue
 * --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    // Remove parent bloat we don't need — keep minimal
    wp_dequeue_style( 'hello-elementor' );
    wp_dequeue_style( 'hello-elementor-theme-style' );

    // Google Fonts — one request, swap for no FOIT
    wp_enqueue_style(
        'bb-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap',
        array(),
        null
    );

    wp_enqueue_style(  'bb-main',           BB_URI . '/assets/css/main.css',          array(), BB_VER );
    wp_enqueue_style(  'bb-header-footer',  BB_URI . '/assets/css/header-footer.css', array( 'bb-main' ), BB_VER );

    if ( is_front_page() || bb_is_home_translation() ) {
        wp_enqueue_style( 'bb-home', BB_URI . '/assets/css/home.css', array( 'bb-main' ), BB_VER );
    }

    wp_enqueue_script( 'bb-main', BB_URI . '/assets/js/main.js', array(), BB_VER, true );
}, 20 );

/* Preconnect to Google Fonts for faster first paint */
add_action( 'wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<meta name="theme-color" content="#E16670">' . "\n";
}, 1 );

/* -----------------------------------------------------------------------
 * Helpers
 * --------------------------------------------------------------------- */

/**
 * Current language slug. URL-first (predictable), Polylang fallback.
 * - /hi/* → 'hi'
 * - /mr/* → 'mr'
 * - everything else (including /panchang/, /, /aarti/) → 'en'
 * URL beats cookie — content matches the URL the user is on.
 */
function bb_current_lang() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ( preg_match( '#^/(hi|mr|gu)(/|$)#', $uri, $m ) ) {
        return $m[1];
    }
    return 'en';
}

/** Echo translated string based on lang; pass array [ 'en' => '...', 'hi' => '...', 'mr' => '...' ] */
function bb_t( $strings ) {
    $lang = bb_current_lang();
    if ( isset( $strings[ $lang ] ) ) {
        echo $strings[ $lang ];
        return;
    }
    if ( isset( $strings['en'] ) ) { echo $strings['en']; return; }
    echo reset( $strings );
}

/** Get site logo URL — falls back to theme asset */
function bb_logo_url() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $img = wp_get_attachment_image_src( $custom_logo_id, 'full' );
        if ( $img ) return $img[0];
    }
    return BB_URI . '/assets/img/logo.png';
}

/** Estimate reading time for a post (minutes) */
function bb_reading_time( $content = null ) {
    if ( null === $content ) $content = get_post_field( 'post_content', get_the_ID() );
    $words = str_word_count( wp_strip_all_tags( $content ) );
    return max( 1, (int) ceil( $words / 200 ) );
}

/** Flag: is this staging? shown as visual badge */
function bb_is_staging() {
    return ( false !== strpos( home_url(), 'staging.bhaktibhawna.com' ) );
}

/* -----------------------------------------------------------------------
 * AdSense — manual placements only, NO Auto Ads
 *
 * Library loads ONLY on single blog posts (is_singular('post')).
 * Homepage, tool pages, archives, static pages = 0 ad bytes.
 *
 * Slots per post: top (after intro), middle (auto-injected after Nth paragraph),
 * bottom (before related posts). Defined as constants so client can swap IDs.
 * --------------------------------------------------------------------- */
define( 'BB_AD_CLIENT', 'ca-pub-4804489090852276' );
if ( ! defined( 'BB_AD_SLOT_TOP' ) )    define( 'BB_AD_SLOT_TOP',    '0000000001' );
if ( ! defined( 'BB_AD_SLOT_MIDDLE' ) ) define( 'BB_AD_SLOT_MIDDLE', '0000000002' );
if ( ! defined( 'BB_AD_SLOT_BOTTOM' ) ) define( 'BB_AD_SLOT_BOTTOM', '0000000003' );
if ( ! defined( 'BB_AD_SLOT_TOOL' ) )   define( 'BB_AD_SLOT_TOOL',   '0000000004' );
if ( ! defined( 'BB_ADS_DISABLED' ) )   define( 'BB_ADS_DISABLED',   false );

/** Render a single AdSense slot. Echoes nothing if BB_ADS_DISABLED. */
function bb_ad_slot( $slot_id, $label = '' ) {
    if ( BB_ADS_DISABLED ) return;
    if ( ! $slot_id ) return;
    ?>
    <div class="bb-ad-slot" aria-label="Advertisement">
        <span class="bb-ad-slot__label">Advertisement</span>
        <ins class="adsbygoogle"
             style="display:block;"
             data-ad-client="<?php echo esc_attr( BB_AD_CLIENT ); ?>"
             data-ad-slot="<?php echo esc_attr( $slot_id ); ?>"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    </div>
    <?php
}

/** Load AdSense library only where ad slots actually render: single posts + tool templates.
 *  Homepage, archives, static pages = 0 ad bytes. */
function bb_should_load_ads() {
    if ( BB_ADS_DISABLED ) return false;
    if ( is_singular( 'post' ) ) return true;
    return is_page_template( array(
        'template-panchang.php', 'template-choghadiya.php', 'template-hora.php',
    ) );
}
add_action( 'wp_head', function () {
    if ( ! bb_should_load_ads() ) return;
    echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr( BB_AD_CLIENT ) . '" crossorigin="anonymous"></script>' . "\n";
}, 5 );

/** Auto-inject middle ad after the Nth <p> in post content (single posts only). */
add_filter( 'the_content', function ( $content ) {
    if ( BB_ADS_DISABLED ) return $content;
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) return $content;

    $paragraph_close = '</p>';
    $insert_after_n  = 3; // after 3rd paragraph
    $count = 0;
    $pos   = 0;
    while ( ( $pos = strpos( $content, $paragraph_close, $pos ) ) !== false ) {
        $count++;
        $pos += strlen( $paragraph_close );
        if ( $count === $insert_after_n ) {
            ob_start();
            bb_ad_slot( BB_AD_SLOT_MIDDLE );
            $ad = ob_get_clean();
            return substr( $content, 0, $pos ) . $ad . substr( $content, $pos );
        }
    }
    return $content;
}, 20 );

/* -----------------------------------------------------------------------
 * Clean up WP head
 * --------------------------------------------------------------------- */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/* Disable emoji bloat */
remove_action( 'wp_head',       'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* -----------------------------------------------------------------------
 * Excerpt
 * --------------------------------------------------------------------- */
add_filter( 'excerpt_length', function () { return 22; }, 999 );
add_filter( 'excerpt_more',   function () { return '…'; } );

/* -----------------------------------------------------------------------
 * Body class — language aware
 * --------------------------------------------------------------------- */
add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'bb-lang-' . bb_current_lang();
    if ( bb_is_staging() ) $classes[] = 'bb-staging';
    return $classes;
} );

/* -----------------------------------------------------------------------
 * Home page IDs across all Polylang translations.
 * Reads Polylang's post_translations taxonomy directly (robust against API shape changes).
 * --------------------------------------------------------------------- */
function bb_home_page_ids() {
    static $ids = null;
    if ( $ids !== null ) return $ids;

    $front_id = (int) get_option( 'page_on_front' );
    $ids = $front_id ? array( $front_id ) : array();

    if ( $front_id ) {
        global $wpdb;
        $row = $wpdb->get_var( $wpdb->prepare(
            "SELECT tt.description FROM {$wpdb->term_taxonomy} tt WHERE tt.taxonomy = %s AND tt.description LIKE %s LIMIT 1",
            'post_translations',
            '%i:' . $front_id . ';%'
        ) );
        if ( $row ) {
            $data = @unserialize( $row );
            if ( is_array( $data ) ) {
                $ids = array_merge( $ids, array_map( 'intval', array_values( $data ) ) );
            }
        }
    }
    $ids = array_values( array_unique( array_filter( $ids ) ) );
    return $ids;
}

function bb_is_home_translation() {
    if ( ! is_page() ) return false;
    return in_array( (int) get_queried_object_id(), bb_home_page_ids(), true );
}

/* -----------------------------------------------------------------------
 * Route translated homepages through front-page.php
 * --------------------------------------------------------------------- */
add_filter( 'template_include', function ( $template ) {
    if ( bb_is_home_translation() ) {
        $front = locate_template( 'front-page.php' );
        if ( $front ) return $front;
    }
    return $template;
}, 99 );

/* -----------------------------------------------------------------------
 * Drop Elementor CSS on pages rendered via our custom templates
 * --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! bb_is_home_translation() && ! is_front_page() ) return;

    wp_dequeue_style( 'elementor-frontend' );
    wp_dequeue_style( 'elementor-icons' );
    wp_dequeue_style( 'elementor-gf-local-roboto' );
    wp_dequeue_style( 'elementor-gf-local-robotoslab' );
    wp_dequeue_style( 'hello-elementor-header-footer' );

    global $wp_styles;
    if ( isset( $wp_styles->registered ) ) {
        foreach ( $wp_styles->registered as $handle => $obj ) {
            if ( strpos( $handle, 'elementor-post-' ) === 0 ) {
                wp_dequeue_style( $handle );
            }
        }
    }
}, 999 );
