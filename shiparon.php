<?php
/**
 * Shiparon
 *
 * @package       Shiparon
 * @author        Shiparon
 * @license       gplv2
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
 * License:       GPLv2
 * License URI:   https://shiparon.com
 *
 * You should have received a copy of the GNU General Public License
 * along with Shiparon. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Include your custom code here.
include(dirname(__FILE__) . '/includes/admin-options.php');
require(dirname(__FILE__) . '/includes/woo-options.php');
