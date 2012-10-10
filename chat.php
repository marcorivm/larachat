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
 * A Shopping Cart based on the Cart library from CodeIgniter for use with
 * the Laravel Framework.
 *
 * @package  Lara-Chat
 * @version  1.0
 * @author   Marco Rivadeneyra <mark@20d.mx>
 * @link     https://github.com/rockiano/Lara-Chat
 */
class Chat
{
	// Adds a nick to the cache
	public static function addNick($id, $nick)
	{
		$users = \Cache::get('online_users');

		if ($users)
		{
			\Cache::forget('online_users');

			// check if nick is already stored
			foreach ($users as $user)
			{
				if ($user[0] == $id)
				{
					$user[1] = $nick;
					\Cache::forever('online_users', $users);
					return;
				}
			}
		}		

		$users[] = array($id, $nick);
		\Cache::forever('online_users', $users);
		return;
	}

	// Gets a nick from the cache
	public static function getNick($id)
	{
		$users = \Cache::get('online_users');

		if ($users)
		{
			foreach($users as $user)
			{
				if ($user[0] == $id)
					return $user[1];
			}
		}

		return null;
	}

	// Remove a user from cache
	public static function removeNick($id)
	{
		$users = \Cache::get('online_users');
		$new_users;

		if ($users)
		{
			foreach($users as $user)
			{
				if ($user[0] != $id)
					$new_users[] = $user;
			}
		}

		return $new_users;
	}

	public static function updateTimestamp($id)
	{
		$user = \User::find($id);
		$user->timestamp();
		$user->save();
	}

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