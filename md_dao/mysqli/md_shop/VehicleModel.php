<?php
class VehicleModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_vehicle');
        $this->log->log_debug('VehicleModel  model be initialized');
    }


}
?>