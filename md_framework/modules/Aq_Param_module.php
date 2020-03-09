<?php
class Aq_Pagination_module {
    private $CI;
    private $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_config();
    }

    public function pagination($page, $count, $pagesize) {
        $html = '<ul class="pagination">';
        $maxPage = ceil($count / $pagesize);
        if ($page * $pagesize > $count) {
            $currentPage = $maxPage;
        } else {
            $currentPage = $page;
        }
        $data['max_page'] = $maxPage;
        $data['current_page'] = $currentPage;
        if ($currentPage == 1) {
            if ($maxPage > 6) {
                $data['start_page'] = 1;
                $data['end_page'] = 7;
            } else {
                $data['start_page'] = 1;
                $data['end_page'] = $maxPage;
            }
        } else if ($currentPage == $maxPage) {
            if ($maxPage > 6) {
                $data['start_page'] = $maxPage - 6;
                $data['end_page'] = $maxPage;
            } else {
                $data['start_page'] = 1;
                $data['end_page'] = $maxPage;
            }
        } else if ($currentPage + 3 > $maxPage) {
            $data['end_page'] = $maxPage;
            if ($currentPage - 3 - ($maxPage - $currentPage) < 1) {
                $data['start_page'] = 1;
            } else {
                $data['start_page'] = $currentPage - 3 - ($maxPage - $currentPage);
            }
        } else {
            if ($currentPage - 3 < 1) {
                $data['start_page'] = 1;
                $data['end_page'] = $currentPage + 3 + (3 - $currentPage);
            } else {
                $data['start_page'] = $currentPage - 3;
                $data['end_page'] = $currentPage + 3;
            }
        }
        return $data;
    }

    public function paginationHtml($page, $count, $pagesize, $uri) {
        if ($count == 0) {
            return '';
        }
        $data = $this->pagination($page, $count, $pagesize);
        $html = '<ul class="pagination">';
        if ($data['current_page'] == 1) {
            $html .= '<li><a class="btn btn-default">第一页</a></li>';
            $html .= '<li><a class="btn btn-default">上一页</a></li>';
        } else {
            $html .= '<li><a class="btn btn-default" href="' . BASE_URL . '/' . $uri . '?page_size=' . $pagesize . '&page=1">第一页</a></li>';
            $html .= '<li><a class="btn btn-default" href="' . BASE_URL . '/' . $uri . '?page_size=' . $pagesize . '&page=' . ($data['current_page'] - 1) . '">上一页</a></li>';
        }
        
        for ($i = $data['start_page']; $i <= $data['end_page']; $i++) {
            if ($i == $data['current_page']) {
                $html .= '<li><a class="btn btn-default pageActive">' . $i . '</a></li>';
            } else {
                $html .= '<li><a class="btn btn-default" href="' . BASE_URL . '/' . $uri . '?page_size=' . $pagesize . '&page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        if ($data['current_page'] == $data['max_page']) {
            $html .= '<li><a class="btn btn-default">下一页</a></li>';
            $html .= '<li><a class="btn btn-default">最终页</a></li>';
        } else {
            $html .= '<li><a class="btn btn-default" href="' . BASE_URL . '/' . $uri . '?page_size=' . $pagesize . '&page=' . ($data['current_page'] + 1) . '">下一页</a></li>';
            $html .= '<li><a class="btn btn-default" href="' . BASE_URL . '/' . $uri .  '?page_size=' . $pagesize . '&page=' . $data['max_page'] . '">最终页</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
}