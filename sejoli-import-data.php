<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://sejoli.co.id
 * @since             1.0.0
 * @package           Sejoli_Import_Data
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - Import Data
 * Plugin URI:        https://https://sejoli.co.id
 * Description:       Plugin for import data sejoli
 * Version:           1.0.0
 * Author:            Sejoli
 * Author URI:        https://https://sejoli.co.id/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-import-data
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_IMPORT_DATA_VERSION', '1.0.0' );
define( 'SEJOLI_IMPORT_DATA_DIR', plugin_dir_path(__FILE__));
define( 'SEJOLI_IMPORT_DATA_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sejoli-import-data-activator.php
 */
function activate_sejoli_import_data() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-import-data-activator.php';
	Sejoli_Import_Data_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sejoli-import-data-deactivator.php
 */
function deactivate_sejoli_import_data() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-import-data-deactivator.php';
	Sejoli_Import_Data_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sejoli_import_data' );
register_deactivation_hook( __FILE__, 'deactivate_sejoli_import_data' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-import-data.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sejoli_import_data() {

	$plugin = new Sejoli_Import_Data();
	$plugin->run();

}
run_sejoli_import_data();
