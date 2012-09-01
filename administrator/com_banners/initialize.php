<?php
//load my custom language
JFactory::getLanguage()->load('com_bannersoverride',__DIR__);

//check if table exists if not will run sql
$db = JFactory::getDbo();
$db->setQuery('SHOW TABLES LIKE "%banner_positions"');
$db->query();
if (!$db->getNumRows())
{
	jimport('joomla.database.driver');
	$buffer = file_get_contents(__DIR__.'/sql/banners.sql');
	// Graceful exit and rollback if read not successful
	if ($buffer === false)
	{
		return false;
	}

	// Create an array of queries from the sql file
	$queries = JDatabaseDriver::splitSql($buffer);

	if (count($queries) == 0)
	{
		// No queries to process
		return 0;
	}

	// Process each query in the $queries array (split out of sql file).
	foreach ($queries as $query)
	{
		$query = trim($query);

		if ($query != '' && $query{0} != '#' && $query{0} != '/*')
		{
			$db->setQuery($query);

			if (!$db->execute())
			{
				return false;
			}
		}
	}
}