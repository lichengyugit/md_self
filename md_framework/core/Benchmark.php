<?php
/**
 * CodeIgniter
 * 
 * @package CodeIgniter
 * @author neowei
 * @copyright Copyright (c) 2015 - 2025, aqdog, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://www.aqdog.com
 * @since Version 3.0
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Benchmark Class
 * This class enables you to mark points and calculate the time difference
 * between them.
 * Memory consumption can also be displayed.
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Libraries
 * @author neowei
 * @link http://codeigniter.com/user_guide/libraries/benchmark.html
 */
class CI_Benchmark {
    
    /**
     * List of all benchmark markers and when they were added
     * 
     * @var array
     */
    public $marker = array();
    
    // --------------------------------------------------------------------
    
    /**
     * Set a benchmark marker
     * Multiple calls to this function can be made so that several
     * execution points can be timed
     * 
     * @access public
     * @param string $name the marker
     * @return void
     */
    public function mark($name) {
        $this->marker[$name] = microtime(TRUE);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Calculates the time difference between two marked points.
     * If the first parameter is empty this function instead returns the
     * {elapsed_time} pseudo-variable. This permits the full system
     * execution time to be shown in a template. The output class will
     * swap the real value for this variable.
     * 
     * @access public
     * @param string	a particular marked point
     * @param string	a particular marked point
     * @param integer	the number of decimal places
     * @return mixed
     */
    public function elapsed_time($point1 = '', $point2 = '', $decimals = 4) {
        if ($point1 === '') {
            return '{elapsed_time}';
        }
        
        if (!isset($this->marker[$point1])) {
            return '';
        }
        
        if (!isset($this->marker[$point2])) {
            $this->marker[$point2] = microtime(TRUE);
        }
        
        return number_format($this->marker[$point2] - $this->marker[$point1], $decimals);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Memory Usage
     * This function returns the {memory_usage} pseudo-variable.
     * This permits it to be put it anywhere in a template
     * without the memory being calculated until the end.
     * The output class will swap the real value for this variable.
     * 
     * @access public
     * @return string
     */
    public function memory_usage() {
        return '{memory_usage}';
    }
}

// END CI_Benchmark class

/* End of file Benchmark.php */
/* Location: ./system/core/Benchmark.php */