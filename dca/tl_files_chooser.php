<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 *
 * PHP version 5
 * @copyright  4ward.media 2011
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @package    filepicker4ward
 * @filesource
 */
 

 /**
 * File management
 */
$GLOBALS['TL_DCA']['tl_files_chooser'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Folder',
		'uploadScript'                => 'fancyUpload',
		'closed'					  => true,
		'onload_callback' => array
		(
			array('tl_files_chooser', 'checkPermission'),
		)
	),

	// List
	'list' => array
	(
		'global_operations' => array
		(
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleNodes'],
				'href'                => 'tg=all',
				'class'               => 'header_toggle'
			),
		),
		'operations' => array
		(
			'choose' => array
			(
				'label'               => array('Datei verwenden', 'Diese Datei auswählen'),
				'href'	  			  => '',
				'attributes'		  => 'onclick="javascript:return insertFile(this);"',
				'icon'				  => 'system/modules/filepicker4ward/html/choose.png'
				
			),
		)
	)
);



class tl_files_chooser extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Check permissions to edit the file system
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Permissions
		if (!is_array($this->User->fop))
		{
			$this->User->fop = array();
		}

		$f1 = $this->User->hasAccess('f1', 'fop');
		$f2 = $this->User->hasAccess('f2', 'fop');
		$f3 = $this->User->hasAccess('f3', 'fop');
		$f4 = $this->User->hasAccess('f4', 'fop');

		// Set the filemounts
		$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['root'] = $this->User->filemounts;

		// Disable the upload button if uploads are not allowed
		if (!$f1)
		{
			$GLOBALS['TL_DCA']['tl_files']['config']['closed'] = true;
		}

		// Disable the edit_all button
		if (!$f2)
		{
			$GLOBALS['TL_DCA']['tl_files']['config']['notEditable'] = true;
		}

		// Disable the delete_all button
		if (!$f3 && !$f4)
		{
			$GLOBALS['TL_DCA']['tl_files']['config']['notDeletable'] = true;
		}

		$session = $this->Session->getData();

		// Set allowed page IDs (edit multiple)
		if (is_array($session['CURRENT']['IDS']))
		{
			if ($this->Input->get('act') == 'editAll' && !$f2)
			{
				$session['CURRENT']['IDS'] = array();
			}

			// Check delete permissions
			else
			{
				$folders = array();
				$delete_all = array();

				foreach ($session['CURRENT']['IDS'] as $id)
				{
					if (is_dir(TL_ROOT . '/' . $id))
					{
						$folders[] = $id;

						if ($f4 || ($f3 && count(scan(TL_ROOT . '/' . $id)) < 1))
						{
							$delete_all[] = $id;
						}
					}
					else
					{
						if (($f3 || $f4) && !in_array(dirname($id), $folders))
						{
							$delete_all[] = $id;
						}
					}
				}

				$session['CURRENT']['IDS'] = $delete_all;
			}
		}

		// Set allowed clipboard IDs
		if (isset($session['CLIPBOARD']['tl_files']) && !$f2)
		{
			$session['CLIPBOARD']['tl_files'] = array();
		}

		// Overwrite session
		$this->Session->setData($session);

		// Check current action
		if ($this->Input->get('act') && $this->Input->get('act') != 'paste')
		{
			switch ($this->Input->get('act'))
			{
				case 'move':
					if (!$f1)
					{
						$this->log('No permission to upload files', 'tl_files checkPermission()', TL_ERROR);
						$this->redirect('contao/main.php?act=error');
					}
					break;

				case 'edit':
				case 'create':
				case 'copy':
				case 'copyAll':
				case 'cut':
				case 'cutAll':
					if (!$f2)
					{
						$this->log('No permission to create, edit, copy or move files', 'tl_files checkPermission()', TL_ERROR);
						$this->redirect('contao/main.php?act=error');
					}
					break;

				case 'delete':
					$strFile = $this->Input->get('id', true);
					if (is_dir(TL_ROOT . '/' . $strFile))
					{
						$files = scan(TL_ROOT . '/' . $strFile);
						if (count($files) && !$f4)
						{
							$this->log('No permission to delete folder "'.$strFile.'" recursively', 'tl_files checkPermission()', TL_ERROR);
							$this->redirect('contao/main.php?act=error');
						}
						elseif (!$f3)
						{
							$this->log('No permission to delete folder "'.$strFile.'"', 'tl_files checkPermission()', TL_ERROR);
							$this->redirect('contao/main.php?act=error');
						}
					}
					elseif (!$f3)
					{
						$this->log('No permission to delete file "'.$strFile.'"', 'tl_files checkPermission()', TL_ERROR);
						$this->redirect('contao/main.php?act=error');
					}
					break;

				default:
					if (count($this->User->fop) < 1)
					{
						$this->log('No permission to manipulate files', 'tl_files checkPermission()', TL_ERROR);
						$this->redirect('contao/main.php?act=error');
					}
					break;
			}
		}
	}

}

?>