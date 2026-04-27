<?php
/**
 * Template Name: Open Source / License Notice
 *
 * AGPL Section 13 source-disclosure page. Linked from the site footer.
 * Content is hard-coded in this template so it ships with the source tree.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$lang = function_exists( 'bb_current_lang' ) ? bb_current_lang() : 'en';

$repo_url     = 'https://github.com/bhaktibhawnatech/bhaktibhawna';
$swisseph_url = 'https://github.com/aloistr/swisseph';
$license_url  = 'https://www.gnu.org/licenses/agpl-3.0.html';

$strings = array(
    'title' => array(
        'en' => 'Open Source',
        'hi' => 'ओपन सोर्स',
        'mr' => 'ओपन सोर्स',
        'gu' => 'ઓપન સોર્સ',
    ),
    'intro' => array(
        'en' => sprintf(
            'This site uses Swiss Ephemeris (Astrodienst) for its astrology calculations. Swiss Ephemeris is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0). In compliance with AGPL Section 13, the complete corresponding source code of the version of this site that runs at <strong>bhaktibhawna.com</strong> is published publicly at the link below.'
        ),
        'hi' => 'यह वेबसाइट ज्योतिषीय गणनाओं के लिए स्विस एफिमेरिस (Astrodienst) का उपयोग करती है, जो GNU Affero General Public License v3.0 (AGPL-3.0) के अंतर्गत लाइसेंस है। AGPL की धारा 13 के अनुपालन में, इस साइट के वर्तमान संस्करण का संपूर्ण स्रोत कोड नीचे दिए गए लिंक पर सार्वजनिक रूप से उपलब्ध है।',
        'mr' => 'ही वेबसाईट ज्योतिषीय गणनांसाठी स्विस एफिमेरिस (Astrodienst) वापरते, जी GNU Affero General Public License v3.0 (AGPL-3.0) अंतर्गत परवानाकृत आहे. AGPL कलम 13 च्या अनुपालनात, या साइटच्या सद्य आवृत्तीचा संपूर्ण स्त्रोत कोड खाली दिलेल्या दुव्यावर सार्वजनिकपणे उपलब्ध आहे.',
        'gu' => 'આ વેબસાઇટ જ્યોતિષ ગણતરીઓ માટે Swiss Ephemeris (Astrodienst) નો ઉપયોગ કરે છે, જે GNU Affero General Public License v3.0 (AGPL-3.0) હેઠળ લાઇસન્સ્ડ છે. AGPL કલમ 13 ના પાલનમાં, આ સાઇટની વર્તમાન આવૃત્તિનો સંપૂર્ણ સ્રોત કોડ નીચે આપેલ લિંક પર જાહેર રીતે ઉપલબ્ધ છે.',
    ),
    'source_label'   => array( 'en' => 'Source code',           'hi' => 'स्रोत कोड',           'mr' => 'स्त्रोत कोड',           'gu' => 'સ્રોત કોડ' ),
    'license_label'  => array( 'en' => 'License',                'hi' => 'लाइसेंस',              'mr' => 'परवाना',                'gu' => 'લાઇસન્સ' ),
    'swisseph_label' => array( 'en' => 'Swiss Ephemeris (upstream)', 'hi' => 'स्विस एफिमेरिस (मूल स्रोत)', 'mr' => 'स्विस एफिमेरिस (मूळ स्त्रोत)', 'gu' => 'Swiss Ephemeris (મૂળ સ્રોત)' ),
    'attrib_heading' => array( 'en' => 'Attributions',           'hi' => 'श्रेय',                'mr' => 'श्रेय',                  'gu' => 'શ્રેય' ),
    'attrib_body'    => array(
        'en' => 'Swiss Ephemeris is © Astrodienst AG, Zurich, Switzerland. WordPress is © WordPress Foundation, GPL-2.0-or-later. All site content (text, images, devotional material) © Bhakti Bhawna unless otherwise noted.',
        'hi' => 'स्विस एफिमेरिस © Astrodienst AG, ज्यूरिख, स्विट्ज़रलैंड का है। WordPress © WordPress Foundation, GPL-2.0-or-later। साइट का सम्पूर्ण सामग्री (पाठ, छवियाँ, धार्मिक सामग्री) अन्यथा निर्दिष्ट न होने पर © भक्ति भवन के अधीन है।',
        'mr' => 'स्विस एफिमेरिस © Astrodienst AG, झुरिक, स्वित्झर्लंड. WordPress © WordPress Foundation, GPL-2.0-or-later. साइटवरील सर्व मजकूर (मजकूर, चित्रे, धार्मिक साहित्य) अन्यथा नमूद नसल्यास © भक्ती भवन यांचा.',
        'gu' => 'Swiss Ephemeris © Astrodienst AG, ઝુરિચ, સ્વિટ્ઝર્લેન્ડ. WordPress © WordPress Foundation, GPL-2.0-or-later. સાઇટની તમામ સામગ્રી (લખાણ, છબીઓ, ધાર્મિક સામગ્રી) અન્યથા ન દર્શાવ્યા સિવાય © ભક્તિ ભવનની છે.',
    ),
);

$T = function( $key ) use ( $strings, $lang ) {
    return $strings[ $key ][ $lang ] ?? $strings[ $key ]['en'];
};

get_header();
?>

<section class="bb-section">
    <div class="bb-container" style="max-width:760px;">
        <header class="bb-section__head" style="text-align:left;">
            <h1><?php echo esc_html( $T( 'title' ) ); ?></h1>
        </header>

        <p style="font-size:1.05rem; line-height:1.7;">
            <?php echo wp_kses_post( $T( 'intro' ) ); ?>
        </p>

        <ul style="line-height:2; font-size:1rem; margin-top:1.5rem;">
            <li><strong><?php echo esc_html( $T( 'source_label' ) ); ?>:</strong>
                <a href="<?php echo esc_url( $repo_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $repo_url ); ?></a>
            </li>
            <li><strong><?php echo esc_html( $T( 'license_label' ) ); ?>:</strong>
                <a href="<?php echo esc_url( $license_url ); ?>" rel="noopener" target="_blank">GNU Affero General Public License v3.0</a>
            </li>
            <li><strong><?php echo esc_html( $T( 'swisseph_label' ) ); ?>:</strong>
                <a href="<?php echo esc_url( $swisseph_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $swisseph_url ); ?></a>
            </li>
        </ul>

        <h2 style="margin-top:2.5rem;"><?php echo esc_html( $T( 'attrib_heading' ) ); ?></h2>
        <p style="line-height:1.7;"><?php echo esc_html( $T( 'attrib_body' ) ); ?></p>
    </div>
</section>

<?php get_footer();
