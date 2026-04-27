<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
</main><!-- /.bb-main -->

<footer class="bb-footer" role="contentinfo">
    <div class="bb-container">
        <div class="bb-footer__grid">
            <div class="bb-footer__brand">
                <div class="bb-footer__logo">
                    <picture>
                        <source srcset="<?php echo esc_url( BB_URI . '/assets/img/logo.webp?v=' . BB_VER ); ?>" type="image/webp">
                        <img src="<?php echo esc_url( BB_URI . '/assets/img/logo.png?v=' . BB_VER ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="600" height="303" loading="lazy">
                    </picture>
                </div>
                <p class="bb-footer__about">
                    <?php bb_t( array(
                        'en' => 'A devoted home for Aarti, Chalisa, Puja vidhi, Panchang, Vastu and astro tools — rooted in the Vaishnav tradition.',
                        'hi' => 'आरती, चालीसा, पूजा विधि, पंचांग, वास्तु और ज्योतिष उपकरणों का एक समर्पित स्थान — वैष्णव परंपरा में।',
                        'mr' => 'आरती, चालीसा, पूजा विधी, पंचांग, वास्तु आणि ज्योतिष साधने — वैष्णव परंपरेत.',
                        'gu' => 'આરતી, ચાલીસા, પૂજા વિધિ, પંચાંગ, વાસ્તુ અને જ્યોતિષ સાધનો માટેનું એક સમર્પિત ઘર — વૈષ્ણવ પરંપરામાં.',
                    ) ); ?>
                </p>
                <div class="bb-footer__social" aria-label="Social">
                    <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0022 12z"/></svg></a>
                    <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.2c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.22.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.05.41 2.22.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.22-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.05.36-2.22.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.22-.41a3.72 3.72 0 01-1.38-.9 3.72 3.72 0 01-.9-1.38c-.16-.42-.36-1.05-.41-2.22C2.21 15.58 2.2 15.2 2.2 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.22.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.05-.36 2.22-.41C8.42 2.21 8.8 2.2 12 2.2zm0 1.8c-3.14 0-3.51.01-4.75.07-1 .05-1.54.22-1.9.36-.48.19-.82.41-1.18.77-.36.36-.58.7-.77 1.18-.14.36-.31.9-.36 1.9-.06 1.24-.07 1.6-.07 4.75s.01 3.51.07 4.75c.05 1 .22 1.54.36 1.9.19.48.41.82.77 1.18.36.36.7.58 1.18.77.36.14.9.31 1.9.36 1.24.06 1.6.07 4.75.07s3.51-.01 4.75-.07c1-.05 1.54-.22 1.9-.36.48-.19.82-.41 1.18-.77.36-.36.58-.7.77-1.18.14-.36.31-.9.36-1.9.06-1.24.07-1.6.07-4.75s-.01-3.51-.07-4.75c-.05-1-.22-1.54-.36-1.9a3.18 3.18 0 00-.77-1.18 3.18 3.18 0 00-1.18-.77c-.36-.14-.9-.31-1.9-.36C15.51 4.01 15.14 4 12 4zm0 3.08a4.92 4.92 0 110 9.84 4.92 4.92 0 010-9.84zm0 1.8a3.12 3.12 0 100 6.24 3.12 3.12 0 000-6.24zm5.06-2.1a1.15 1.15 0 110 2.3 1.15 1.15 0 010-2.3z"/></svg></a>
                    <a href="#" aria-label="YouTube"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 12s0-3.47-.44-5.13a2.64 2.64 0 00-1.86-1.86C19.04 4.57 12 4.57 12 4.57s-7.04 0-8.7.44a2.64 2.64 0 00-1.86 1.86C1 8.53 1 12 1 12s0 3.47.44 5.13a2.64 2.64 0 001.86 1.86c1.66.44 8.7.44 8.7.44s7.04 0 8.7-.44a2.64 2.64 0 001.86-1.86C23 15.47 23 12 23 12zM9.75 15.27V8.73L15.5 12l-5.75 3.27z"/></svg></a>
                    <a href="#" aria-label="WhatsApp"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.9 11.9 0 0012 0C5.37 0 0 5.37 0 12c0 2.12.55 4.14 1.6 5.96L0 24l6.22-1.63A11.9 11.9 0 0012 24c6.63 0 12-5.37 12-12 0-3.2-1.25-6.22-3.48-8.52zM12 22a9.9 9.9 0 01-5.06-1.38l-.36-.21-3.69.97.99-3.6-.24-.37A9.96 9.96 0 012 12c0-5.51 4.49-10 10-10s10 4.49 10 10-4.49 10-10 10zm5.46-7.47c-.3-.15-1.78-.88-2.05-.98-.27-.1-.47-.15-.67.15-.2.3-.77.98-.95 1.18-.17.2-.35.22-.65.07a8.2 8.2 0 01-2.41-1.49 9.05 9.05 0 01-1.67-2.08c-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.48-.5-.67-.51-.17-.01-.37-.01-.57-.01-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49 0 1.47 1.07 2.89 1.22 3.09.15.2 2.12 3.23 5.13 4.53.72.31 1.28.5 1.71.64.72.23 1.37.2 1.89.12.58-.09 1.78-.73 2.03-1.43.25-.7.25-1.3.17-1.43-.07-.12-.27-.2-.57-.35z"/></svg></a>
                </div>
            </div>

            <div>
                <h4><?php bb_t( array( 'en' => 'Explore', 'hi' => 'अन्वेषण', 'mr' => 'शोध', 'gu' => 'શોધખોળ' ) ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/aarti/' ) ); ?>"><?php bb_t( array( 'en' => 'Aarti', 'hi' => 'आरती', 'mr' => 'आरती', 'gu' => 'આરતી' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/chalisa/' ) ); ?>"><?php bb_t( array( 'en' => 'Chalisa', 'hi' => 'चालीसा', 'mr' => 'चालीसा', 'gu' => 'ચાલીસા' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/puja/' ) ); ?>"><?php bb_t( array( 'en' => 'Puja Vidhi', 'hi' => 'पूजा विधि', 'mr' => 'पूजा विधी', 'gu' => 'પૂજા વિધિ' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/astro/' ) ); ?>"><?php bb_t( array( 'en' => 'Astro Tools', 'hi' => 'ज्योतिष', 'mr' => 'ज्योतिष', 'gu' => 'જ્યોતિષ' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/vastu/' ) ); ?>"><?php bb_t( array( 'en' => 'Vastu', 'hi' => 'वास्तु', 'mr' => 'वास्तु', 'gu' => 'વાસ્તુ' ) ); ?></a></li>
                </ul>
            </div>

            <?php
            $f_lang = bb_current_lang();
            $tool_url = function( $en, $hi, $mr, $gu ) use ( $f_lang ) {
                $m = array( 'en' => $en, 'hi' => $hi, 'mr' => $mr, 'gu' => $gu );
                return home_url( $m[ $f_lang ] ?? $en );
            };
            ?>
            <div>
                <h4><?php bb_t( array( 'en' => 'Tools', 'hi' => 'उपकरण', 'mr' => 'साधने', 'gu' => 'સાધનો' ) ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( $tool_url( '/en/panchang/', '/hi/aaj-ka-panchang/', '/mr/aajcha-panchang/', '/gu/aaj-no-panchang/' ) ); ?>"><?php bb_t( array( 'en' => 'Panchang', 'hi' => 'पंचांग', 'mr' => 'पंचांग', 'gu' => 'પંચાંગ' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( $tool_url( '/en/choghadiya/', '/hi/aaj-ka-choghadiya/', '/mr/aajcha-choghadiya/', '/gu/aaj-nu-choghadiya/' ) ); ?>"><?php bb_t( array( 'en' => 'Choghadiya', 'hi' => 'चौघड़िया', 'mr' => 'चौघडिया', 'gu' => 'ચોઘડિયું' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( $tool_url( '/en/hora/', '/hi/aaj-ka-hora/', '/mr/aajcha-hora/', '/gu/aaj-no-hora/' ) ); ?>"><?php bb_t( array( 'en' => 'Hora', 'hi' => 'होरा', 'mr' => 'होरा', 'gu' => 'હોરા' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/kundli/' ) ); ?>"><?php bb_t( array( 'en' => 'Kundli', 'hi' => 'कुंडली', 'mr' => 'कुंडली', 'gu' => 'કુંડળી' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/rashifal/' ) ); ?>"><?php bb_t( array( 'en' => 'Rashifal', 'hi' => 'राशिफल', 'mr' => 'राशीभविष्य', 'gu' => 'રાશિફળ' ) ); ?></a></li>
                </ul>
            </div>

            <div>
                <h4><?php bb_t( array( 'en' => 'Connect', 'hi' => 'संपर्क', 'mr' => 'संपर्क', 'gu' => 'સંપર્ક' ) ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php bb_t( array( 'en' => 'Contact', 'hi' => 'संपर्क', 'mr' => 'संपर्क', 'gu' => 'સંપર્ક' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/write-for-us/' ) ); ?>"><?php bb_t( array( 'en' => 'Write for Us', 'hi' => 'हमारे लिए लिखें', 'mr' => 'आमच्यासाठी लिहा', 'gu' => 'અમારા માટે લખો' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php bb_t( array( 'en' => 'Privacy', 'hi' => 'गोपनीयता', 'mr' => 'गोपनीयता', 'gu' => 'ગોપનીયતા' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php bb_t( array( 'en' => 'Terms', 'hi' => 'शर्तें', 'mr' => 'अटी', 'gu' => 'શરતો' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/open-source/' ) ); ?>"><?php bb_t( array( 'en' => 'Open Source', 'hi' => 'ओपन सोर्स', 'mr' => 'ओपन सोर्स', 'gu' => 'ઓપન સોર્સ' ) ); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="bb-footer__bottom">
            <span>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php bb_t( array( 'en' => 'All rights reserved.', 'hi' => 'सर्वाधिकार सुरक्षित।', 'mr' => 'सर्व हक्क राखीव.', 'gu' => 'સર્વ હક્કો સુરક્ષિત.' ) ); ?></span>
            <span class="bb-devanagari">🕉 श्री कृष्णाय नमः</span>
        </div>
    </div>
</footer>

<?php if ( bb_is_staging() ) : ?>
    <div class="bb-staging-badge" aria-hidden="true">STAGING · NOINDEX</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
