<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocacartLog
{
	/* type - type : e.g warning, error, etc.
	 * typeid - for example order id, category id, product id
	 *
	 * Example:
	 * PhocacartLog::add(1, 'Message', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername);
	 */
	
	public static function add( $type = 0, $title = '', $typeid = 0, $description = '') {

		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$enable_logging		= $paramsC->get( 'enable_logging', 0 );
		
		if ($enable_logging == 0) {
			return false;
		}
	
		if ((int)$type > 0 && $title != '' ) {
			$uri			= JFactory::getUri();
			$user			= PhocacartUser::getUser();
			$db				= JFactory::getDBO();
			$ip 			= $_SERVER["REMOTE_ADDR"];
			$incoming_page	= htmlspecialchars($uri->toString());
			$userid			= 0;
			if (isset($user->id) && (int)$user->id > 0) {
				$userid = $user->id;
			}
			
			// Ordering
			$ordering = 0;
			$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_logs');
			$max = $db->loadResult();
			$ordering = $max+1;
			
			
			
			$query = ' INSERT INTO #__phocacart_logs ('
			.$db->quoteName('user_id').', '
			.$db->quoteName('type_id').', '
			.$db->quoteName('type').', '
			.$db->quoteName('title').', '
			.$db->quoteName('ip').', '
			.$db->quoteName('incoming_page').', '
			.$db->quoteName('description').', '
			.$db->quoteName('published').', '
			.$db->quoteName('ordering').', '
			.$db->quoteName('date').' )'
			. ' VALUES ('
			.$db->quote((int)$userid).', '
			.$db->quote((int)$typeid).', '
			.$db->quote((int)$type).', '
			.$db->quote($title).', '
			.$db->quote($ip).', '
			.$db->quote($incoming_page).', '
			.$db->quote($description).', '
			.$db->quote('1').', '
			.$db->quote((int)$ordering).', '
			.$db->quote(gmdate('Y-m-d H:i:s')).' )';
			
			$db->setQuery($query);
			$db->execute();

			return true;
		}
		return false;
		
	}
}
?>