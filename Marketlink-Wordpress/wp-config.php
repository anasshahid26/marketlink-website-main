<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'marketli_mohsin');

/** MySQL database username */
define('DB_USER', 'marketli_mohsin');

/** MySQL database password */
define('DB_PASSWORD', 'mohsin123');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';B!k)QCqt&g7tLi/[Ww-4w#|[m:V8z{~n5Y4XM>+8{t+mn+U8|?/V|<Hb71=:$lY');
define('SECURE_AUTH_KEY',  'l_O%=,oEc#|rtmgv]{y*Xxa|KJ0ps-R|twnxZ#QWA;k(FtH52.= {tvQvhY+VFQD');
define('LOGGED_IN_KEY',    '&l3+=N:0prr;cdf4I&(Z}/]4gh[5(sKa@6Y{oo&zXB[9)5>)m-{Al]ETuK7>1 n}');
define('NONCE_KEY',        'p7:_@9[+Gw:So9[|u}6RHcuLL7{#&Mmlt,/|[OaE4I3{<`|HqV-BInS-=?(8o,?4');
define('AUTH_SALT',        'HZbiEksUs]ra0Z<K&v`Zxs%|4|2P_+nS.Do>H2)6XViaSa 9U9miW3m>j-~uzhUA');
define('SECURE_AUTH_SALT', 'NOg5bg+.t#?iW8n>RaHGF 4Tzez5)hz[1|`HuHWS^T_5(6tJo>GG}</KBs$FZ%3M');
define('LOGGED_IN_SALT',   'K~@FCnf>ajxl9=I :hUs}=Rqvf,Q-1XOaOh=M8DCx;i{}Ij+bUMhD?bVbEX]?[Hs');
define('NONCE_SALT',       '<xyG1XBtKLj#I;7i5P{mty,!t}WG38jJ#1wQn{hVEZgxacTi&bkDs#n$WMq:+O-B');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
