<?php
/**
 * BB_Astro — local Swiss-Ephemeris-backed astrology calculations.
 *
 * Architecture: a Node daemon at 127.0.0.1:8917 owns the SwissEph WASM
 * (loaded once at startup, ~128MB resident). PHP calls it via HTTP.
 *
 * Daemon: /home/u970630969/sweph/jsastro/astro-server.js
 *         launched by /home/u970630969/sweph/jsastro/start-server.sh
 *         heartbeat: hPanel cron every 5 minutes runs start-server.sh
 *
 * Why daemon over proc_open: Hostinger LVE memory cap on PHP-FPM workers
 * causes WASM init to OOM when Node is spawned as a child. Long-running
 * daemon runs outside that cap and amortizes the WASM init cost.
 *
 * Each method matches the JSON shape that BB_Prokerala_API returned, so
 * existing templates need ZERO changes when swapped over.
 *
 * Caches every response for 24h via WP transients (per date+location).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BB_Astro {

    const ENDPOINT     = 'http://127.0.0.1:8917';
    const TIMEOUT      = 10;
    const CACHE_PREFIX = 'bb_astro_';

    const DEFAULT_LAT = 28.6139;
    const DEFAULT_LNG = 77.2090;
    const DEFAULT_TZ  = 'Asia/Kolkata';

    /**
     * POST to a daemon route with JSON body. Cached 24h on success.
     *
     * @param string $route     e.g. '/hora'
     * @param array  $args      associative — date, lat, lng, tz, …
     * @param int    $cache_ttl seconds (default 24h)
     * @return array|null
     */
    private static function call( $route, $args, $cache_ttl = DAY_IN_SECONDS ) {
        $cache_key = self::CACHE_PREFIX . md5( $route . wp_json_encode( $args ) );
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) return $cached;

        $resp = wp_remote_post( self::ENDPOINT . $route, array(
            'timeout' => self::TIMEOUT,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => wp_json_encode( $args ),
        ) );

        if ( is_wp_error( $resp ) ) {
            error_log( 'BB_Astro ' . $route . ' error: ' . $resp->get_error_message() );
            return null;
        }
        $code = wp_remote_retrieve_response_code( $resp );
        $body = wp_remote_retrieve_body( $resp );
        if ( $code !== 200 ) {
            error_log( "BB_Astro $route HTTP $code body=" . substr( $body, 0, 500 ) );
            return null;
        }
        $data = json_decode( $body, true );
        if ( ! is_array( $data ) ) {
            error_log( "BB_Astro $route non-JSON body=" . substr( $body, 0, 500 ) );
            return null;
        }

        set_transient( $cache_key, $data, $cache_ttl );
        return $data;
    }

    /**
     * Normalize args to: date, lat, lng, tz. Mirrors ProKerala's defaults.
     */
    private static function normalize_args( $args ) {
        $tz = $args['tz'] ?? self::DEFAULT_TZ;
        $lat = isset( $args['lat'] ) ? (float) $args['lat'] : self::DEFAULT_LAT;
        $lng = isset( $args['lng'] ) ? (float) $args['lng'] : self::DEFAULT_LNG;

        if ( ! empty( $args['date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $args['date'] ) ) {
            $date = $args['date'];
        } else {
            try {
                $dt = new DateTime( 'now', new DateTimeZone( $tz ) );
                $date = $dt->format( 'Y-m-d' );
            } catch ( Exception $e ) {
                $date = date( 'Y-m-d' );
            }
        }
        return array( 'date' => $date, 'lat' => $lat, 'lng' => $lng, 'tz' => $tz );
    }

    /**
     * Hora (planetary hours).
     */
    public static function hora( $args = array() ) {
        return self::call( '/hora', self::normalize_args( $args ) );
    }

    /**
     * Choghadiya (16 muhurats: 8 day + 8 night). Shape: { muhurat:[…16], sunrise, sunset }.
     */
    public static function choghadiya( $args = array() ) {
        return self::call( '/choghadiya', self::normalize_args( $args ) );
    }

    /**
     * Full panchang. Shape: { tithi[], nakshatra[], yoga[], karana[], vaara,
     *   sunrise, sunset, moonrise, moonset, auspicious_period[], inauspicious_period[], ritu }.
     */
    public static function panchang( $args = array() ) {
        return self::call( '/panchang', self::normalize_args( $args ) );
    }

    /**
     * Drik + Vedic Ritu only. Shape: { drik_ritu:{name}, vedic_ritu:{name} }.
     */
    public static function ritu( $args = array() ) {
        return self::call( '/ritu', self::normalize_args( $args ), WEEK_IN_SECONDS );
    }

    /**
     * Chandra Bala — favorable Janma Rashis for current Moon position.
     * Shape: { chandra_bala: [ { end, rasis:[{name}, ...] } ] }
     */
    public static function chandra_bala( $args = array() ) {
        return self::call( '/chandra-bala', self::normalize_args( $args ) );
    }

    /**
     * Tara Bala — 9 Taras × 3 Janma Nakshatras each.
     * Shape: { tara_bala: [ { name, type, end, nakshatras:[{name}, ...] }, …9 ] }
     */
    public static function tara_bala( $args = array() ) {
        return self::call( '/tara-bala', self::normalize_args( $args ) );
    }

    /** Daemon health check — useful for admin / debugging. */
    public static function health() {
        $resp = wp_remote_get( self::ENDPOINT . '/health', array( 'timeout' => 2 ) );
        if ( is_wp_error( $resp ) ) return array( 'ok' => false, 'error' => $resp->get_error_message() );
        return json_decode( wp_remote_retrieve_body( $resp ), true );
    }

    /** Force-clear all cached responses. */
    public static function purge_cache() {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_' . self::CACHE_PREFIX . '%',
            '_transient_timeout_' . self::CACHE_PREFIX . '%'
        ) );
    }
}
