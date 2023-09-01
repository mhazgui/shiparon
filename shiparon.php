<?php
/**
 * Shiparon
 *
 * @package       Shiparon
 * @author        Shiparon
 * @license       gplv3
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Shiparon
 * Plugin URI:    https://shiparon.com
 * Description:   This is some demo short description...
 * Version:       1.0.0
 * Author:        Shiparon
 * Author URI:    https://shiparon.com
 * Text Domain:   Shiparon
 * Domain Path:   /languages
 * License:       GPLv3
 * License URI:   https://shiparon.com
 *
 * You should have received a copy of the GNU General Public License
 * along with Shiparon. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

// Exit if accessed directly to enhance security.
if ( ! defined( 'ABSPATH' ) ) exit;

// Include admin options. This page will get the API end point and use it later to post shipment info to the carrier website.
include(dirname(__FILE__) . '/includes/admin-options.php');
// Include WooCommerce options. This file will add a news options meta box in the order page .
require(dirname(__FILE__) . '/includes/woo-options.php');
