<? namespace Larachat;

/**
 * --------------------------------------------------------------------------
 * What we can use in this class
 * --------------------------------------------------------------------------
 */
use Laravel\Session;
use Laravel\View;
use Larachat\Models\User;

/**
 * --------------------------------------------------------------------------
 * Lara Cart
 * --------------------------------------------------------------------------
 *
 * A simple chat for use with the Laravel Framework.
 *
 * @package  Lara-Chat
 * @version  1.0
 * @author   Marco Rivadeneyra <mark@20d.mx>
 * @link     https://github.com/rockiano/Lara-Chat
 */
class Chat
{
	public static function create($user = null)
	{
		// CSS
		\Laravel\Asset::add('Bootstrap-CSS', 'assets/css/bootstrap.min.css');
		\Laravel\Asset::add('Custom-CSS','assets/css/custom.css');
		\Laravel\Asset::add('Custom-CSS3', 'assets/css/custom_css3.css');
		// JS
		\Laravel\Asset::add('jQuery','assets/js/jquery-1.8.1.min.js');
		\Laravel\Asset::add('Bootstrap-JS', 'assets/js/bootstrap.min.js');
		\Laravel\Asset::add('Custom-JS', 'assets/js/custom.js');

		if(is_object($user)) {
			$user = new User($user);
			return View::make('larachat::home.index')->with('user', $user);			
		} else {
			return 'Invalid user';
		}
	}
}