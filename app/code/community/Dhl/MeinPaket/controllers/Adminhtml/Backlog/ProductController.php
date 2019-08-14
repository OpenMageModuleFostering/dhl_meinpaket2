<?php
class Dhl_MeinPaket_Adminhtml_Backlog_ProductController extends Mage_Adminhtml_Controller_Action {
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'meinpaket/backlog' )->_addBreadcrumb ( Mage::helper ( 'meinpaket' )->__ ( 'Backlog' ), Mage::helper ( 'meinpaket' )->__ ( 'Backlog' ) );
		return $this;
	}
	public function indexAction() {
		$this->_initAction ()->renderLayout ();
	}
	public function exportCsvAction() {
		$fileName = 'backlog.csv';
		$grid = $this->getLayout ()->createBlock ( 'meinpaket/adminhtml_backlog_product_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getCsvFile () );
	}
	public function exportExcelAction() {
		$fileName = 'backlog.xml';
		$grid = $this->getLayout ()->createBlock ( 'meinpaket/adminhtml_backlog_product_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getExcelFile ( $fileName ) );
	}
	public function massDeleteAction() {
		$backlogIds = $this->getRequest ()->getParam ( 'backlogIds' );
		if (! is_array ( $backlogIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'meinpaket' )->__ ( 'Please select backlog entries.' ) );
		} else {
			try {
				$backlogModel = Mage::getModel ( 'meinpaket/backlog_product' );
				foreach ( $backlogIds as $backlogId ) {
					$backlogModel->load ( $backlogId )->delete ();
				}
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'meinpaket' )->__ ( 'Total of %d record(s) were deleted.', count ( $backlogIds ) ) );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			}
		}
		$this->_redirect ( '*/*/index' );
	}
	
	/**
	 * Product grid for AJAX request.
	 * Sort and filter result for example.
	 */
	public function gridAction() {
		$this->loadLayout ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'meinpaket/adminhtml_backlog_product_grid' )->toHtml () );
	}
	
	/**
	 * Mass action: schedule
	 *
	 * @return void
	 */
	public function scheduleAction() {
		$cronjobs = $this->getCronjobs ();
		Mage::helper ( 'meinpaket/cron' )->scheduleJobs ( $cronjobs, true );
		$this->_redirect ( '*/*/index' );
	}
	
	/**
	 * Mass action: run
	 *
	 * @return void
	 */
	public function runAction() {
		$cronjobs = $this->getCronjobs ();
		Mage::helper ( 'meinpaket/cron' )->runJobs ( $cronjobs, true );
		$this->_redirect ( '*/*/index' );
	}
	
	/**
	 * Get cronjobs from request.
	 *
	 * @return array of cronjobs
	 */
	private function getCronjobs() {
		$cronjob = $this->getRequest ()->getParam ( 'cronjob', 'all' );
		if ($cronjob == 'all') {
			return Dhl_MeinPaket_Model_Cron::$CRONJOBS;
		} else if (in_array ( $cronjob, Dhl_MeinPaket_Model_Cron::$CRONJOBS )) {
			return array (
					$cronjob 
			);
		}
		return null;
	}
}
