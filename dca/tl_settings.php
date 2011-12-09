<?php if(!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 *
 * PHP version 5
 * @copyright  4ward.media 2011
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @package    filepicker4ward
 * @filesource
 */


$GLOBALS['TL_DCA']['tl_settings']['fields']['filepicker_replaceFileTree'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['filepicker_replaceFileTree'],
	'inputType'	=> 'checkbox',
	'eval'		=> array('tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_ireplace('{backend_legend}','{backend_legend},filepicker_replaceFileTree',$GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);
?>