<?php
class Dhl_MeinPaket_Block_Adminhtml_Async extends Mage_Adminhtml_Block_Widget_Grid_Container {
	/**
	 * Constructor for Cron Adminhtml Block
	 */
	public function __construct() {
		$this->_blockGroup = 'meinpaket';
		$this->_controller = 'adminhtml_async';
		$this->_headerText = Mage::helper ( 'meinpaket' )->__ ( 'Async' );
		parent::__construct ();
	}
	/**
	 * Prepare layout
	 *
	 * @return Dhl_MeinPaket_Block_Adminhtml_Backlog_Product_Grid_Container
	 */
	protected function _prepareLayout() {
		$this->removeButton ( 'add' );
		
		$this->_addButton ( 'run_async', array (
				'label' => Mage::helper ( 'meinpaket' )->__ ( 'Synchronize Async Jobs' ),
				'onclick' => 'setLocation(\'' . $this->getUrl ( '*/*/run', array (
						'cronjob' => Dhl_MeinPaket_Model_Cron::SYNC_ASYNC 
				) ) . '\')',
				'class' => 'add' 
		) );
		
		return parent::_prepareLayout ();
	}
}