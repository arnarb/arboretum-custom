<?php /* BEGIN KINSTA STAGING ENVIRONMENT */ ?>
<?php if ( !defined('KINSTA_DEV_ENV') ) { define('KINSTA_DEV_ENV', true); /* Kinsta staging - don't remove this line */ } ?>
<?php if ( !defined('JETPACK_STAGING_MODE') ) { define('JETPACK_STAGING_MODE', true); /* Kinsta staging - don't remove this line */ } ?>
<?php /* END KINSTA STAGING ENVIRONMENT */ ?>
<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'arnoldarboretumwebsite' );

/** MySQL database username */
define( 'DB_USER', 'arnoldarboretumwebsite' );

/** MySQL database password */
define( 'DB_PASSWORD', 'SZipROuoX8Eka3x' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
// define('WP_MEMORY_LIMIT', '10000M');
/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '08rJF$C6CUi0;8O1VvfjRdv*Y/h?.*LSE>ClhG$j`Ft oT>(#,D_3DV|![N1JiL%' );
define( 'SECURE_AUTH_KEY',   '#Z&b%URey4,};!y 7ON2(%J{e@>vZ-BHe~to;CLL`b1y4mzw]!*ceAlEKOE1k5~s' );
define( 'LOGGED_IN_KEY',     'iom3w{gNoAS1PK${F<,D)rjdHrtUyyRcP|G!Ht*7,-QY{`I$Jip0d$eAI:GA6=cs' );
define( 'NONCE_KEY',         '3fH.h7#=U;gd}8Wsw@PGuG_s+{t=2$G9n>~@OVDjFO%5gUK9UtSuYD?~*ciJS8jT' );
define( 'AUTH_SALT',         'RD|O_Fvb@=bUf<0LuTe|Zy1u+@47,z63K6$vbm=!u69O5N,e<I<KTtV{.$kev$pm' );
define( 'SECURE_AUTH_SALT',  'As+kx35M2.KSogaEl%j,.f+<%+%]9!Bl9k:2ZEm3~K) }YyK/My#JMWKUr{uqd%q' );
define( 'LOGGED_IN_SALT',    'OHIS2*s2xn.G</M9>g`t9)Fv%lTVV# )yt{v |~yTVR=]G;L]QYe+?HNf:/$2I~`' );
define( 'NONCE_SALT',        'x$=%i:O*9DoO0?O0:{f1m(H($**qW.vSTmL/+B`wH=TqfTTY=(&~QxosI7^01@4.' );
define( 'WP_CACHE_KEY_SALT', '<0!w|ilWC)1UKP[8Af$!KFXNs^Df^z%M--CjVE7cCC}n-.2T2g6vN_Nn;|*Pw<JA' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/* Algolia API keys */

define( 'ALGOLIA_APPLICATION_ID', 'FM6OP5R7TP');
define( 'ALGOLIA_ADMIN_API_KEY', '5744d98ad3638124ff6d15e0796c10db');
define( 'ALGOLIA_SEARCH_ONLY_API_KEY', 'a5638a5b52e24eec05af1cf4b22838c3');
define( 'ALGOLIA_INDEX_PREFIX', 'staging');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
