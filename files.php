<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../initialize.php');


/**
 * Class FileManager
 *
 * Popup file manager controller.
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class FileManager extends Backend
{

	/**
	 * Current Ajax object
	 * @var object
	 */
	protected $objAjax;


	/**
	 * Initialize the controller
	 * 
	 * 1. Import the user
	 * 2. Call the parent constructor
	 * 3. Authenticate the user
	 * 4. Load the language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		// System::getReferer checks for the scriptName → hack it ;)
		$this->Environment->script = 'contao/files.php';

		$this->User->authenticate();

		$this->loadLanguageFile('default');
		$this->loadLanguageFile('modules');
	}


	/**
	 * Run the controller and parse the login template
	 */
	public function run()
	{
		//
		$GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready',function(){

	// add clear selection button
	var clearBtn = $('tl_buttons').getElement('a.header_new_folder').clone();
	clearBtn.setStyles({
			'background-image':'url(system/themes/default/images/delete.gif)'
		})
		.set('text','{$GLOBALS['TL_LANG']['MSC']['resetSelected']}')
		.set('href','javascript:clearChoice();');

	var clearBtnContainer = new Element('div',{
		'id':'tl_buttons_a',
		'styles':{
			'margin-top':'5px'
		}
	});
	clearBtn.inject(clearBtnContainer);
	clearBtnContainer.inject($('tl_buttons'),'after');
});

var parentInput = parent.document.getElement('input[name=\"".urldecode($this->Input->get('fld'))."\"]');
var parentImg = parentInput.getParent().getElement('img.preview');

function clearChoice()
{
	parentImg.set('src','system/modules/filepicker4ward/html/nofile.png');
	parentInput.value = '';
	parent.Mediabox.close();
}

function insertFile(el)
{
	el = $(el);

	var erg = el.get('href').match(/id=([^&]+)/);
	parentInput.value = decodeURI(erg[1]);
	parentImg.set('title',erg[1]);

	// try to use preview image
	var imgs = el.getParent('li').getElements('img');
	for(var i=0;i<imgs.length;i++)
	{
		if(imgs[i] != null && imgs[i].get('src').indexOf('system/html') != -1)
		{
			parentImg.set('src',imgs[i].get('src'));
			parent.Mediabox.close();
			return false;
		}
	}

	// show a icon
	var ext = el.get('href').match(/id=[^\.]+\.([a-zA-Z0-9]+)/);
	if(ext == null)
	{
		// probably a folder has been choosen
		parentImg.set('src','system/modules/filepicker4ward/html/folder.png');
		parent.Mediabox.close();
	}
	else
	{
		// choose the icon for this filetype
		var imgFile = 'system/modules/filepicker4ward/html/icons/'+ext[1]+'.png';
		Asset.image(imgFile,{
			onLoad: function()
			{
				parentImg.set('src',imgFile);
				parent.Mediabox.close();
			},
			onError: function()
			{
				parentImg.set('src','system/modules/filepicker4ward/html/default.png');
				parent.Mediabox.close();
			}
		});
	}


	return false;
}
</script>
";

		// insert select-choice button
		$this->loadDataContainer('tl_files');
		$GLOBALS['TL_DCA']['tl_files']['list']['operations']['choose'] =  array
		(
			'label'               => array('Datei verwenden', 'Diese Datei auswählen'),
			'href'	  			  => '',
			'attributes'		  => 'onclick="javascript:return insertFile(this);"',
			'icon'				  => 'system/modules/filepicker4ward/html/choose.png'

		);

		// set valid filetypes
		if($this->Input->get('ext') && preg_match("~^[a-z0-9,]+~i",$this->Input->get('ext')))
		{
			$GLOBALS['TL_DCA']['tl_files']['config']['validFileTypes'] = $this->Input->get('ext');
		}

		// Hack to display the expanded tree
		$sessionOld = $this->Session->getData();
		$sessionTmp = $sessionOld;
		$sessionTmp['filetree'] = array();
		if($this->Input->get('f'))
		{
			$f = $this->Input->get('f');
			$f = html_entity_decode(urldecode($f));
			$f = str_replace('==PUNKT==', '.', $f);
			if(file_exists(TL_ROOT.'/'.$f))
			{
				$currFolder = TL_ROOT;
				$pieces = explode('/', substr($f,0,strrpos($f,'/')));
				foreach($pieces as $folder)
				{
					$currFolder .= '/'.$folder;
					$sessionTmp['filetree'][md5($currFolder)] = 1;
				}
			}
		}
		$this->Session->setData($sessionTmp);




		$this->Template = new BackendTemplate('be_files');
		$this->Template->main = '';

		// Ajax request
		if ($this->Environment->isAjaxRequest)
		{
			$this->objAjax = new Ajax($this->Input->post('action'));
			$this->objAjax->executePreActions();
		}

		$this->Template->main .= $this->getBackendModule('files');

		// Default headline
		if ($this->Template->headline == '')
		{
			$this->Template->headline = $GLOBALS['TL_CONFIG']['websiteTitle'];
		}

		$this->Template->theme = $this->getTheme();
		$this->Template->base = $this->Environment->base;
		$this->Template->language = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->pageOffset = $this->Input->cookie('BE_PAGE_OFFSET');
		$this->Template->error = ($this->Input->get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
		$this->Template->skipNavigation = $GLOBALS['TL_LANG']['MSC']['skipNavigation'];
		$this->Template->request = ampersand($this->Environment->request);
		$this->Template->top = $GLOBALS['TL_LANG']['MSC']['backToTop'];
		$this->Template->expandNode = $GLOBALS['TL_LANG']['MSC']['expandNode'];
		$this->Template->collapseNode = $GLOBALS['TL_LANG']['MSC']['collapseNode'];
		$this->Template->loadingData = $GLOBALS['TL_LANG']['MSC']['loadingData'];

		$this->Template->output();
	}
}


/**
 * Instantiate the controller
 */
$objFileManager = new FileManager();
$objFileManager->run();

?>