<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<section class="bb-section" style="min-height: 50vh; display:flex; align-items:center;">
    <div class="bb-container" style="max-width: 640px; text-align:center;">
        <span style="font-size: 4rem;">🕉</span>
        <h1 style="margin-top: var(--bb-sp-2);"><?php bb_t( array( 'en' => 'Page not found', 'hi' => 'पृष्ठ नहीं मिला', 'mr' => 'पृष्ठ सापडले नाही' ) ); ?></h1>
        <span class="bb-ornament"></span>
        <p class="bb-lede bb-mx-auto">
            <?php bb_t( array(
                'en' => 'The page you are looking for has moved or no longer exists. Let\'s find something beautiful instead.',
                'hi' => 'आप जिस पृष्ठ की तलाश कर रहे हैं वह स्थानांतरित हो गया है या अब मौजूद नहीं है। चलिए कुछ सुंदर खोजते हैं।',
                'mr' => 'तुम्ही शोधत असलेले पृष्ठ हलवले आहे किंवा आता अस्तित्वात नाही. चला काहीतरी सुंदर शोधूया.',
            ) ); ?>
        </p>
        <div style="margin-top: var(--bb-sp-3); display:flex; gap: 0.75rem; justify-content:center; flex-wrap:wrap;">
            <a class="bb-btn bb-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bb_t( array( 'en' => 'Return Home', 'hi' => 'घर वापस जाएँ', 'mr' => 'घरी परत जा' ) ); ?></a>
            <a class="bb-btn bb-btn--ghost" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php bb_t( array( 'en' => 'Read the Blog', 'hi' => 'ब्लॉग पढ़ें', 'mr' => 'ब्लॉग वाचा' ) ); ?></a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
