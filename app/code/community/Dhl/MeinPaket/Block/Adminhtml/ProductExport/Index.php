<?php

/**
 * Block for the overview step of the product export.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Block_Adminhtml_ProductExport
 * @version		$Id$
 * @author		Daniel Pötzinger <daniel.poetzinger@aoemedia.de>
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Block_Adminhtml_ProductExport_Index extends Mage_Adminhtml_Block_Template {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct ();
		$this->assign ( 'exportLabel', 'Exportiere Produkte' );
	}
}
