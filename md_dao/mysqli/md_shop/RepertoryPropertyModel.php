<?php
class RepertoryPropertyModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_repertory_property');
        $this->log->log_debug('RepertoryPropertyModel  model be initialized');
    }


}
?>