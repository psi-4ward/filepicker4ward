<?php if(!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * @copyright 4ward.media 2011 <http://www.4wardmedia.de>
 * @author Christoph Wiechert <wio@psitrax.de>
 */

/**
 * Helper class for filepicker4ward
 */
class Filepicker4wardHelper extends System
{

	/**
	 * Replace inputType of fields with single-file filetrees
	 * @param string$table
	 */
	public function replaceFiletrees($table)
	{
		if(!isset($GLOBALS['TL_DCA'][$table]['fields']) || !is_array($GLOBALS['TL_DCA'][$table]['fields'])) return;

		foreach($GLOBALS['TL_DCA'][$table]['fields'] as $name => $fld)
		{
			if($fld['inputType'] == 'fileTree' && $fld['eval']['fieldType'] == 'radio')
			{
				$GLOBALS['TL_DCA'][$table]['fields'][$name]['inputType'] = 'filepicker4ward';
			}
		}
	}
}
