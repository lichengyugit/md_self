<?php
class VehicleLogModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_vehicle_log');
        $this->log->log_debug('VehicleLogModel  model be initialized');
    }


}
?>