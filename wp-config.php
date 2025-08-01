<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'y:RAoFM%r1aLL4t$vNAkV~A2 fPW@Tr9vZ=I Y#!zM)qNwZ~XHc!tITZm)c$=*q ' );
define( 'SECURE_AUTH_KEY',   'X/f2?0tQ!DKkg4~6kcZCJr~joLGGx<}sT7uv;.UL#0J-s 1K3t_-*%<LTE0`cP<G' );
define( 'LOGGED_IN_KEY',     'K9J:J~5]2-.7:(T`|DN[H/00bM4Z[;%F. #N{iiHqO*f]%nfV2RkSHEfag0e.z&}' );
define( 'NONCE_KEY',         'O1tX#0.djuyK?zMj^}I/C%US4z!YD&x#yg|!a10JWOH9&h!.z:}M7J#&cjz@gcme' );
define( 'AUTH_SALT',         'UhF3iEn1zr]w^Bq*GgJ1NpBD[NmiivrBF FQ?(YbemqO8fN|)TT.hti|kkL`~:9U' );
define( 'SECURE_AUTH_SALT',  'z5OH:Ngo6ADeZ`#?)H:/_7/y]3r5x/9~N`;mGZUcA?GLL$?%$bPaovj+9kWi&!rJ' );
define( 'LOGGED_IN_SALT',    'M6o`Rx#fxeytOP%H*,yTjZ=w~pX[7ZY;k^(NXRGZa&3R;(p3mr*_)E6jvJHKX4H3' );
define( 'NONCE_SALT',        'hZz?[1zlv^R4z2O1&X@J?>@3|P>lDz]$j_k<xO(JCjS,<t(SB%&nxE`#Qd0rgX(i' );
define( 'WP_CACHE_KEY_SALT', 'r3o m.#k&OP(AsF|g`8YB@;|tvFN@YlPV,2:z$jlfXn|LW6l3^~<=eu5RZXs3VRh' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
