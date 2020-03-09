<?php
class BrandSeriesModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_brand_series');
        $this->log->log_debug('BrandSeriesModel  model be initialized');
    }


}
?>