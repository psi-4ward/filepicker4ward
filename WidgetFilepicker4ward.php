<?php

class WidgetFilepicker4ward extends Widget
{
	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';

	
	/**
	 * Validate
	 * @param mixed
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		return parent::validator($varInput);
	}

	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		return '<a href="system/modules/filepicker4ward/files.php" onclick="Mediabox.open([[this.get(\'href\')+\'?ext='.urlencode($this->extensions).'&f=\'+encodeURI(this.getNext(\'input\').get(\'value\').replace(\'.\',\'==PUNKT==\'))+\'&fld=\'+encodeURI(this.getNext(\'input\').get(\'name\')),\'\',\'800 600\']],0);return false;" title="'.$this->varValue.'">'.$this->getThumb($this->varValue).'</a>'.
			sprintf('<input type="hidden" name="%s" id="ctrl_%s" class="tl_text" value="%s" onfocus="Backend.getScrollOffset();">',
							$this->strName,
							$this->strId,
							specialchars($this->varValue)
					);
	}	
	
	
	protected function getThumb($var)
	{
		if(!strlen($var))
			return '<img src="system/modules/filepicker4ward/html/nofile.png" class="preview" alt="">';

		$objFile = new File($this->varValue);
		if ($GLOBALS['TL_CONFIG']['thumbnails'] && $objFile->isGdImage && $objFile->height <= $GLOBALS['TL_CONFIG']['gdMaxImgHeight'] && $objFile->width <= $GLOBALS['TL_CONFIG']['gdMaxImgWidth'])
		{
			$_height = ($objFile->height < 70) ? $objFile->height : 70;
			$_width = (($objFile->width * $_height / $objFile->height) > 400) ? 90 : '';
			return '<img src="'. $this->getImage($this->varValue, $_width, $_height) . '" alt="" class="preview">';
		}			
			
		$ext = strtolower(substr($this->varValue,strrpos($this->varValue,'.')+1));
		if(file_exists(TL_ROOT."/system/modules/filepicker4ward/html/icons/".$ext.'.png'))
		{
			return '<img src="system/modules/filepicker4ward/html/icons/'.$ext.'.png" class="preview" alt="">';	
		}
		else
		{
			return '<img src="system/modules/filepicker4ward/html/default.png" class="preview" alt="">';
		}		
	}
	
	
	
	
}