<?php
/**
 * Navex
 *
 * @package       NAVEX
 * @author        Navex
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Navex
 * Plugin URI:    https://navex.tn
 * Description:   This is some demo short description...
 * Version:       1.0.0
 * Author:        Navex
 * Author URI:    https://navex.tn
 * Text Domain:   navex
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://navex.tn
 *
 * You should have received a copy of the GNU General Public License
 * along with Navex. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Include your custom code here.
include(dirname(__FILE__) . '//includes/admin-options.php');
require(dirname(__FILE__) . '/includes/woo-options.php');