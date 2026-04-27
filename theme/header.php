<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#bb-main"><?php esc_html_e( 'Skip to content', 'bhaktibhawna' ); ?></a>

<?php
// Language switcher links (Polylang-aware)
$langs = array();
if ( function_exists( 'pll_the_languages' ) ) {
    $langs = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 1 ) );
}
$current_lang = bb_current_lang();
?>

<div class="bb-topbar">
    <div class="bb-container bb-topbar__inner">
        <span class="bb-topbar__message">
            <?php bb_t( array(
                'en' => '🕉 Daily Panchang · Aarti · Chalisa · Astro Tools',
                'hi' => '🕉 दैनिक पंचांग · आरती · चालीसा · ज्योतिष उपकरण',
                'mr' => '🕉 दैनिक पंचांग · आरती · चालीसा · ज्योतिष साधने',
                'gu' => '🕉 દૈનિક પંચાંગ · આરતી · ચાલીસા · જ્યોતિષ સાધન',
            ) ); ?>
        </span>
        <?php if ( ! empty( $langs ) ) : ?>
            <div class="bb-topbar__lang" role="navigation" aria-label="Language">
                <?php foreach ( $langs as $l ) :
                    $active = ( $l['slug'] === $current_lang ) ? ' is-active' : '';
                    $label  = strtoupper( $l['slug'] );
                ?>
                    <a href="<?php echo esc_url( $l['url'] ); ?>" class="bb-lang-link<?php echo $active; ?>" hreflang="<?php echo esc_attr( $l['slug'] ); ?>"><?php echo esc_html( $label ); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<header class="bb-header" role="banner">
    <div class="bb-container bb-header__inner">
        <a class="bb-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
            <picture>
                <source srcset="<?php echo esc_url( BB_URI . '/assets/img/logo.webp?v=' . BB_VER ); ?>" type="image/webp">
                <img src="<?php echo esc_url( BB_URI . '/assets/img/logo.png?v=' . BB_VER ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="600" height="303" fetchpriority="high">
            </picture>
        </a>

        <button class="bb-nav-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'bhaktibhawna' ); ?>" aria-expanded="false" aria-controls="bb-primary-nav">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M3 6h18M3 12h18M3 18h18"/>
            </svg>
        </button>

        <nav id="bb-primary-nav" class="bb-nav" role="navigation" aria-label="Primary">
            <button class="bb-nav__close" aria-label="<?php esc_attr_e( 'Close menu', 'bhaktibhawna' ); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M6 18L18 6"/>
                </svg>
            </button>
            <?php
            /* Lang-aware menu with icons. 4-lang URLs for tools, simpler URLs for content sections. */
            $menu_lang = bb_current_lang();
            $menu_items = array(
                array( 'icon' => '🏠', 'label' => array( 'en' => 'Home', 'hi' => 'होम', 'mr' => 'होम', 'gu' => 'હોમ' ),
                       'url' => array( 'en' => '/en/', 'hi' => '/hi/', 'mr' => '/mr/', 'gu' => '/gu/' ) ),
                array( 'icon' => '📅', 'label' => array( 'en' => 'Panchang', 'hi' => 'पंचांग', 'mr' => 'पंचांग', 'gu' => 'પંચાંગ' ),
                       'url' => array( 'en' => '/en/panchang/', 'hi' => '/hi/aaj-ka-panchang/', 'mr' => '/mr/aajcha-panchang/', 'gu' => '/gu/aaj-no-panchang/' ) ),
                array( 'icon' => '⏳', 'label' => array( 'en' => 'Choghadiya', 'hi' => 'चौघड़िया', 'mr' => 'चौघडिया', 'gu' => 'ચોઘડિયું' ),
                       'url' => array( 'en' => '/en/choghadiya/', 'hi' => '/hi/aaj-ka-choghadiya/', 'mr' => '/mr/aajcha-choghadiya/', 'gu' => '/gu/aaj-nu-choghadiya/' ) ),
                array( 'icon' => '🪐', 'label' => array( 'en' => 'Hora', 'hi' => 'होरा', 'mr' => 'होरा', 'gu' => 'હોરા' ),
                       'url' => array( 'en' => '/en/hora/', 'hi' => '/hi/aaj-ka-hora/', 'mr' => '/mr/aajcha-hora/', 'gu' => '/gu/aaj-no-hora/' ) ),
                array( 'icon' => '🪔', 'label' => array( 'en' => 'Aarti', 'hi' => 'आरती', 'mr' => 'आरती', 'gu' => 'આરતી' ),
                       'url' => array( 'en' => '/aarti/', 'hi' => '/hi/aarti/', 'mr' => '/mr/aarti/', 'gu' => '/gu/aarti/' ) ),
                array( 'icon' => '📿', 'label' => array( 'en' => 'Chalisa', 'hi' => 'चालीसा', 'mr' => 'चालीसा', 'gu' => 'ચાલીસા' ),
                       'url' => array( 'en' => '/chalisa/', 'hi' => '/hi/chalisa/', 'mr' => '/mr/chalisa/', 'gu' => '/gu/chalisa/' ) ),
                array( 'icon' => '🛕', 'label' => array( 'en' => 'Puja Vidhi', 'hi' => 'पूजा विधि', 'mr' => 'पूजा विधी', 'gu' => 'પૂજા વિધિ' ),
                       'url' => array( 'en' => '/puja/', 'hi' => '/hi/puja/', 'mr' => '/mr/puja/', 'gu' => '/gu/puja/' ) ),
                array( 'icon' => '✨', 'label' => array( 'en' => 'Astro', 'hi' => 'ज्योतिष', 'mr' => 'ज्योतिष', 'gu' => 'જ્યોતિષ' ),
                       'url' => array( 'en' => '/astro/', 'hi' => '/hi/astro/', 'mr' => '/mr/astro/', 'gu' => '/gu/astro/' ) ),
                array( 'icon' => '🏛', 'label' => array( 'en' => 'Vastu', 'hi' => 'वास्तु', 'mr' => 'वास्तू', 'gu' => 'વાસ્તુ' ),
                       'url' => array( 'en' => '/vastu/', 'hi' => '/hi/vastu/', 'mr' => '/mr/vastu/', 'gu' => '/gu/vastu/' ) ),
                array( 'icon' => '📖', 'label' => array( 'en' => 'Blog', 'hi' => 'ब्लॉग', 'mr' => 'ब्लॉग', 'gu' => 'બ્લોગ' ),
                       'url' => array( 'en' => '/blog/', 'hi' => '/hi/blog/', 'mr' => '/mr/blog/', 'gu' => '/gu/blog/' ) ),
            );
            echo '<ul class="bb-menu">';
            foreach ( $menu_items as $item ) {
                $url   = $item['url'][ $menu_lang ]   ?? $item['url']['en'];
                $label = $item['label'][ $menu_lang ] ?? $item['label']['en'];
                printf(
                    '<li><a href="%s"><span class="bb-menu__icon" aria-hidden="true">%s</span><span class="bb-menu__label">%s</span></a></li>',
                    esc_url( home_url( $url ) ),
                    esc_html( $item['icon'] ),
                    esc_html( $label )
                );
            }
            echo '</ul>';
            ?>
        </nav>

        <a class="bb-btn bb-btn--primary bb-header__cta" href="<?php echo esc_url( home_url( '/astro/' ) ); ?>">
            <?php bb_t( array( 'en' => 'Astro Tools', 'hi' => 'ज्योतिष उपकरण', 'mr' => 'ज्योतिष साधने', 'gu' => 'જ્યોતિષ સાધન' ) ); ?>
        </a>
    </div>
</header>

<div class="bb-nav-backdrop" aria-hidden="true"></div>

<main id="bb-main" role="main" class="bb-main">
