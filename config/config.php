<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 *
 * PHP version 5
 * @copyright  4ward.media 2011
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @package    filepicker4ward
 * @filesource
 */

// Widget
$GLOBALS['BE_FFL']['filepicker4ward'] = 'WidgetFilepicker4ward';

// DCA-Hook to replace filetree widget
if($GLOBALS['TL_CONFIG']['filepicker_replaceFileTree'])
	$GLOBALS['TL_HOOKS']['loadDataContainer']['filepicker4ward'] = array('Filepicker4wardHelper','replaceFiletrees');

?>