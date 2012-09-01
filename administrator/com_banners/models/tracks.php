<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of tracks.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersModelTracks extends BannersModelTracksDefault
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Get the application object
		$app = JFactory::getApplication();

		require_once dirname(__DIR__).'/helpers/banners.php';

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				'a.track_date as track_date,'.
				'a.track_type as track_type,'.
				'a.'.$db->quoteName('count'), ' as '.$db->quoteName('count')
		);
		$query->from($db->quoteName('#__banner_tracks').' AS a');

		// Join with the banners
		$query->join('LEFT', $db->quoteName('#__banners').' as b ON b.id=a.banner_id');
		$query->select('b.name as name');

		// Join with the client
		$query->join('LEFT', $db->quoteName('#__banner_clients').' as cl ON cl.id=b.cid');
		$query->select('cl.name as client_name');

		// Join with the category
		$query->join('LEFT', $db->quoteName('#__categories').' as cat ON cat.id=b.catid');
		$query->select('cat.title as category_title');

		// Filter by type
		$type = $this->getState('filter.type');
		if (!empty($type)) {
			$query->where('a.track_type = '.(int) $type);
		}

		// Filter by client
		$clientId = $this->getState('filter.client_id');
		if (is_numeric($clientId)) {
			$query->where('b.cid = '.(int) $clientId);
		}

		// Filter by category
		$catedoryId = $this->getState('filter.category_id');
		if (is_numeric($catedoryId)) {
			$query->where('b.catid = '.(int) $catedoryId);
		}

		// Filter by begin date

		$begin = $this->getState('filter.begin');
		if (!empty($begin)) {
			$query->where('a.track_date >= '.$db->Quote($begin));
		}

		// Filter by end date
		$end = $this->getState('filter.end');
		if (!empty($end)) {
			$query->where('a.track_date <= '.$db->Quote($end));
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'name');
		$query->order($db->escape($orderCol).' '.$db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}
}