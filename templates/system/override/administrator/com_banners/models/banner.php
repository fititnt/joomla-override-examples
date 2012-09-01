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
 * Banner model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersModelBanner extends BannersModelBannerDefault
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$return = parent::save($data);
		
		// Process the menu link mappings.
		$assignment = isset($data['assignment']) ? $data['assignment'] : 0;

		// Delete old module to menu item associations
		// $db->setQuery(
		//	'DELETE FROM #__modules_menu'.
		//	' WHERE moduleid = '.(int) $table->id
		// );

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete();
		$query->from('#__banners_menu');
		$query->where('moduleid = ' . (int) $table->id);
		$db->setQuery((string) $query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// If the assignment is numeric, then something is selected (otherwise it's none).
		if (is_numeric($assignment))
		{
			// Variable is numeric, but could be a string.
			$assignment = (int) $assignment;

			// Logic check: if no module excluded then convert to display on all.
			if ($assignment == -1 && empty($data['assigned']))
			{
				$assignment = 0;
			}

			// Check needed to stop a module being assigned to `All`
			// and other menu items resulting in a module being displayed twice.
			if ($assignment === 0)
			{
				// Assign new module to `all` menu item associations.
				// $this->_db->setQuery(
				//  'INSERT INTO #__modules_menu'.
				//  ' SET moduleid = ' . (int) $table->id . ', menuid = 0'
				// );

				$query->clear();
				$query->insert('#__banners_menu');
				$query->columns(array($db->quoteName('bannerid'), $db->quoteName('menuid')));
				$query->values((int) $table->id . ', 0');
				$db->setQuery((string) $query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}
			elseif (!empty($data['assigned']))
			{
				// Get the sign of the number.
				$sign = $assignment < 0 ? -1 : +1;

				// Preprocess the assigned array.
				$tuples = array();
				foreach ($data['assigned'] as &$pk)
				{
					$tuples[] = '(' . (int) $table->id . ',' . (int) $pk * $sign . ')';
				}

				$this->_db->setQuery(
					'INSERT INTO #__banners_menu (moduleid, menuid) VALUES ' .
					implode(',', $tuples)
				);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}
		}
		
		return $return;
	}
}