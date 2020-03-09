<?php
class BrandSeriesTypeModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_brand_series_type');
        $this->log->log_debug('BrandSeriesTypeModel  model be initialized');
    }


}
?>