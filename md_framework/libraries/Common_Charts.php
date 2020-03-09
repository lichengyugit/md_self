<?php
class CI_Common_Charts {
    var $CI;
    var $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_log();
        $this->log->log_debug('Common_Charts class be initialized');
    }

    public function piechart($data, $title, $deepath, $filename, $type) {
        $path = XML_PATH . '/' . $type . '/' . $deepath . DS . $filename;
        // if (!file_exists($path)) {
        $chart = new \Libchart\View\Chart\PieChart(500, 300);
        $dataSet = new \Libchart\Model\XYDataSet();
        foreach ($data as $key=>$value) {
            $dataSet->addPoint(new \Libchart\Model\Point($key, $value));
        }
        $chart->setDataSet($dataSet);
        $chart->setTitle($title);
        $chart->render($path);
        // }
    }

    public function histogram($data, $title, $deepath, $filename, $type) {
        $path = XML_PATH . '/' . $type . '/' . $deepath . DS . $filename;
        // if (!file_exists($path)) {
        $chart = new \Libchart\View\Chart\VerticalBarChart(500, 300);
        $dataSet = new \Libchart\Model\XYDataSet();
        foreach ($data as $key=>$value) {
            $dataSet->addPoint(new \Libchart\Model\Point($key, $value));
        }
        $chart->setDataSet($dataSet);
        $chart->setTitle($title);
        $chart->render($path);
        // }
    }

    public function getBase64Image($deepath, $filename, $type) {
        $path = XML_PATH . '/' . $type . '/' . $deepath . DS . $filename;
        $type = getimagesize($path); // 取得图片的大小，类型等
        $fp = fopen($path, "r");
        $file_content = chunk_split(base64_encode(fread($fp, filesize($path)))); // base64编码
        switch ($type[2]) { // 判读图片类型
        case 1 :
            $img_type = "gif";
            break;
        case 2 :
            $img_type = "jpg";
            break;
        case 3 :
            $img_type = "png";
            break;
        }
        $img = 'data:image/' . $img_type . ';base64,' . $file_content; // 合成图片的base64编码
        fclose($fp);
        return $img;
    }
}