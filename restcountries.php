<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://arieverschoor.com
 * @since             1.0.0
 * @package           Restcountries
 *
 * @wordpress-plugin
 * Plugin Name:       RestCountries
 * Plugin URI:        https://comm-on.nu
 * Description:       Get country data
 * Version:           1.0.0
 * Author:            Arie Verschoor
 * Author URI:        https://arieverschoor.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       restcountries
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
define( 'RESTCOUNTRIES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-restcountries-activator.php
 */
function activate_restcountries() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-restcountries-activator.php';
	Restcountries_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-restcountries-deactivator.php
 */
function deactivate_restcountries() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-restcountries-deactivator.php';
	Restcountries_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_restcountries' );
register_deactivation_hook( __FILE__, 'deactivate_restcountries' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-restcountries.php';



add_action('wp_ajax_save_restcountries', 'save_restcountries');
add_action('wp_ajax_nopriv_save_restcountries','save_restcountries');

function save_restcountries() {
    $restcountries = get_option('restcountries');

    if (isset($_POST['name']) ) {

		array_push($restcountries, $_POST['name']);

 		update_option('restcountries', $restcountries);

		$data = array(
			'success' => true,
			'message' => sprintf(esc_html__('Country %s added', 'restcountries'), $_POST['name'])
		);
	
		//wp_send_json_success( $data );
		echo json_encode($data);
		wp_die();
  
    } else {
      wp_send_json_error();
    }
  
}


function shortcode_restcountries($atts) {

    if (is_string($atts)) {
        $atts = array();
    }       
  
    $atts = shortcode_atts(array(
        'title'     => null,
        'level'     => 0,
        'limit'     => -1,
        'width'     => 256,
        'height'    => 128,
        'orderby'   => 'menu_order',

  
    ), $atts, 'restcountries');
  
    extract( $atts, EXTR_SKIP );

    ?>

	<div class="container">
		<div class="row">
			<div class="col-sm">
			<?php
				$res = wp_remote_get('https://restcountries.com/v3.1/all', array('method' => 'GET'));

				$res_body = wp_remote_retrieve_body($res);
				$country_data = json_decode($res_body, true);
				
				if ( $country_data ):
					echo "<select class='restcountries-select'>\n";
					echo "<option value='---' data-flag='' data-language='' data-currency=''>[Select country]</option>\n";

					foreach( $country_data as $c_id => $country ):
						$languages = $country['languages'] ?? array();
						$language = $currency = array();

						foreach ($languages as $single_language) {
							array_push($language, $single_language);
						}
						$currencies = $country['currencies'] ?? array();
						foreach ($currencies as $all_currency) {
							foreach ($all_currency as $curr_code => $single_currency) {
								array_push($currency, $single_currency);
							}
						}
						$borders = $country['borders'] ?? array();
						echo "<option value='" . $country['fifa'] . "' data-name='" . $country['name']['common'] . "' data-flag='" . $country['flags']['png'] . "' data-language='" . implode(', ', $language) . "' data-currency='" . implode(', ', $currency) . "' data-currency='" . implode(', ', $currency) . "' data-borders='" . implode(', ', $borders) . "'>" . $country['name']['common'] . "</option>\n";
					endforeach;
					echo "</select>\n";
				endif;
			?>
			</div>
			<div class="col-sm">
				Name: <strong><span class="restcountries-select-name"></span></strong>
				<span class="restcountries-select-borders" style="display: none;"></span></strong>
			</div>
			<div class="col-sm">
				Flag: <span class="restcountries-select-flag"></span>
			</div>
			<div class="col-sm">
				Language: <strong><span class="restcountries-select-language"></span></strong>
			</div>
			<div class="col-sm">
				Currency: <strong><span class="restcountries-select-currency"></span></strong>
			</div>
			<div class="col-sm">
				<div class="restcountries-select-button" style="display: none;">
					<button class="restcountries-save" data-ajaxurl="<?php echo admin_url( 'admin-ajax.php' ); ?>">Save to list</button>
				</div>
			</div>
		</div>

		<div class="row">
			<h3>Favorite countries:</h3>
			<div class="col-sm restcountries-list">
				<?php
					$countries_list = get_option('restcountries');
					if ($countries_list) :
						foreach ($countries_list as $country) {
							$res = wp_remote_get('https://restcountries.com/v3.1/name/' . $country, array('method' => 'GET'));
							$res_body = wp_remote_retrieve_body($res);
							$country_data = json_decode($res_body, true);
							if ($country_data[0]['borders']) {
								echo $country . ' (borders: '. implode(', ' , $country_data[0]['borders']) . ')<br/>';
							}
							else {
								echo $country . '(borders:)<br/>';
							}
						}
					endif;
				?>

			</div>
		</div>
	</div>

    <?php
  }
  

add_shortcode('restcountries', 'shortcode_restcountries');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_restcountries() {

	$plugin = new Restcountries();
	$plugin->run();

}
run_restcountries();
