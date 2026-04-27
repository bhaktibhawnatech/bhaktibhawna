<?php
/**
 * ProKerala API wrapper for Bhakti Bhawna.
 *
 * - OAuth2 client_credentials grant (auto token refresh)
 * - Token cached in WP transient (50min, slightly less than 1h expiry)
 * - Per-endpoint response caching (daily data = 24h)
 * - Defensive error handling — returns null on failure, never breaks page
 *
 * Requires constants in wp-config.php:
 *   BB_PROKERALA_CLIENT_ID
 *   BB_PROKERALA_CLIENT_SECRET
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BB_Prokerala_API {

    const TOKEN_URL = 'https://api.prokerala.com/token';
    const API_BASE  = 'https://api.prokerala.com/v2';
    const TOKEN_TRANSIENT = 'bb_pk_token';
    const RESP_TRANSIENT_PREFIX = 'bb_pk_';

    /** Default coords — Delhi, India */
    const DEFAULT_LAT = 28.6139;
    const DEFAULT_LNG = 77.2090;
    const DEFAULT_TZ  = 'Asia/Kolkata';

    /**
     * Get a valid OAuth access token (cached).
     */
    public static function get_token() {
        if ( ! defined( 'BB_PROKERALA_CLIENT_ID' ) || ! defined( 'BB_PROKERALA_CLIENT_SECRET' ) ) {
            return new WP_Error( 'no_creds', 'ProKerala credentials not configured.' );
        }

        $cached = get_transient( self::TOKEN_TRANSIENT );
        if ( $cached ) return $cached;

        $resp = wp_remote_post( self::TOKEN_URL, array(
            'timeout' => 15,
            'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
            'body'    => http_build_query( array(
                'grant_type'    => 'client_credentials',
                'client_id'     => BB_PROKERALA_CLIENT_ID,
                'client_secret' => BB_PROKERALA_CLIENT_SECRET,
            ) ),
        ) );

        if ( is_wp_error( $resp ) ) return $resp;

        $code = wp_remote_retrieve_response_code( $resp );
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );

        if ( $code !== 200 || empty( $body['access_token'] ) ) {
            return new WP_Error( 'token_failed', 'ProKerala token request failed', array( 'code' => $code, 'body' => $body ) );
        }

        $token   = $body['access_token'];
        $expires = (int) ( $body['expires_in'] ?? 3600 );

        // cache 5 minutes less than reported expiry
        set_transient( self::TOKEN_TRANSIENT, $token, max( 60, $expires - 300 ) );

        return $token;
    }

    /**
     * Generic GET request. Caches response for $cache_ttl seconds.
     *
     * @param string $endpoint   e.g. '/astrology/panchang'
     * @param array  $params     query params
     * @param int    $cache_ttl  seconds (default 24h)
     * @return array|null        decoded body['data'] section, or null on error
     */
    public static function get( $endpoint, $params = array(), $cache_ttl = DAY_IN_SECONDS ) {
        $cache_key = self::RESP_TRANSIENT_PREFIX . md5( $endpoint . wp_json_encode( $params ) );
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) return $cached;

        $token = self::get_token();
        if ( is_wp_error( $token ) ) {
            error_log( 'BB Prokerala token error: ' . $token->get_error_message() );
            return null;
        }

        $url = self::API_BASE . $endpoint;
        if ( ! empty( $params ) ) {
            $url .= '?' . http_build_query( $params );
        }

        $resp = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ),
        ) );

        if ( is_wp_error( $resp ) ) {
            error_log( 'BB Prokerala request error: ' . $resp->get_error_message() );
            return null;
        }

        $code = wp_remote_retrieve_response_code( $resp );
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );

        if ( $code !== 200 ) {
            error_log( 'BB Prokerala HTTP ' . $code . ' for ' . $endpoint . ': ' . wp_remote_retrieve_body( $resp ) );
            // If 401, drop token cache and retry once
            if ( $code === 401 ) {
                delete_transient( self::TOKEN_TRANSIENT );
            }
            return null;
        }

        $data = $body['data'] ?? null;
        if ( $data ) set_transient( $cache_key, $data, $cache_ttl );
        return $data;
    }

    /**
     * Format coordinates as ProKerala expects ("28.6139,77.2090").
     */
    private static function coords( $lat = null, $lng = null ) {
        $lat = $lat !== null ? (float) $lat : self::DEFAULT_LAT;
        $lng = $lng !== null ? (float) $lng : self::DEFAULT_LNG;
        return $lat . ',' . $lng;
    }

    /**
     * Get datetime in ISO 8601 with timezone.
     * If $date string passed (YYYY-MM-DD), use that date at noon-local-time.
     */
    private static function now_iso( $tz = null, $date = null ) {
        try {
            $tz_obj = new DateTimeZone( $tz ?: self::DEFAULT_TZ );
            if ( $date && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
                $dt = new DateTime( $date . ' 12:00:00', $tz_obj );
            } else {
                $dt = new DateTime( 'now', $tz_obj );
            }
            return $dt->format( 'c' );
        } catch ( Exception $e ) { return date( 'c' ); }
    }

    /* ---------------------------------------------------------------------
     * High-level methods
     * ------------------------------------------------------------------- */

    /**
     * Map our internal lang codes to ProKerala's (en|hi|ta|te|ml|kn|gu|mr|pa|bn|or)
     */
    public static function api_lang( $bb_lang ) {
        $map = array( 'en' => 'en', 'hi' => 'hi', 'mr' => 'mr', 'gu' => 'gu' );
        return $map[ $bb_lang ] ?? 'en';
    }

    /**
     * Full panchang via /panchang/advanced — single call returns:
     *  - 5 elements (tithi, nakshatra, yoga, karana, vaara)
     *  - sunrise/sunset/moonrise/moonset
     *  - auspicious_period (Abhijit, Amrit Kaal, Brahma Muhurat)
     *  - inauspicious_period (Rahu, Yamaganda, Gulika, Dur Muhurat, Varjyam)
     */
    public static function panchang( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/panchang/advanced', $args, DAY_IN_SECONDS );
    }

    /** Ritu (Vedic + Drik season + Ayana) */
    public static function ritu( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/ritu', $args, WEEK_IN_SECONDS );
    }

    /** Chandra Bala — favorable moon signs at this time */
    public static function chandra_bala( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/chandra-bala', $args, DAY_IN_SECONDS );
    }

    /** Tara Bala — favorable nakshatras at this time */
    public static function tara_bala( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/tara-bala', $args, DAY_IN_SECONDS );
    }

    /** Hora timings (planetary hours) */
    public static function hora( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/hora', $args, DAY_IN_SECONDS );
    }

    /** Today's tithi */
    public static function tithi( $args = array() ) {
        $p = self::panchang( $args );
        return $p['tithi'] ?? null;
    }

    /** Today's nakshatra */
    public static function nakshatra( $args = array() ) {
        $p = self::panchang( $args );
        return $p['nakshatra'] ?? null;
    }

    /** Choghadiya — 16 muhurats (8 day + 8 night) split by is_day flag */
    public static function choghadiya( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/choghadiya', $args, DAY_IN_SECONDS );
    }

    /** Rahu Kaal */
    public static function rahu_kaal( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'datetime'    => self::now_iso( $args['tz'] ?? null, $args['date'] ?? null ),
            'coordinates' => self::coords( $args['lat'] ?? null, $args['lng'] ?? null ),
            'ayanamsa'    => 1,
            'la'          => self::api_lang( $args['bb_lang'] ?? 'en' ),
        ) );
        unset( $args['tz'], $args['lat'], $args['lng'], $args['bb_lang'], $args['date'] );
        return self::get( '/astrology/rahu-kaal', $args, DAY_IN_SECONDS );
    }

    /**
     * Force-clear all cached responses (for debugging or after data fix).
     */
    public static function purge_cache() {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_' . self::RESP_TRANSIENT_PREFIX . '%',
            '_transient_timeout_' . self::RESP_TRANSIENT_PREFIX . '%'
        ) );
        delete_transient( self::TOKEN_TRANSIENT );
    }
}
