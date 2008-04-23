<?php
/* $Id: functions.inc.php 34 2005-03-03 20:27:58Z justin $ */
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE) {
    $total_pages = ceil($num_items/$per_page);
    if ($total_pages == 1 ) {
        return '';
    }
    $on_page = floor($start_item / $per_page) + 1;
    $page_string = '';
    if ($total_pages > 10 ) {
        $init_page_max = ($total_pages > 3 ) ? 3 : $total_pages;
        for($i = 1; $i < $init_page_max + 1; $i++) {
            $page_string .= ($i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1 ) * $per_page ) . '">' . $i . '</a>';
            if ($i < $init_page_max ) {
                $page_string .= ", ";
            }
        }
        if ($total_pages > 3 ) {
            if ($on_page > 1 && $on_page < $total_pages ) {
                $page_string .= ($on_page > 5 ) ? ' ... ' : ', ';
                $init_page_min = ($on_page > 4 ) ? $on_page : 5;
                $init_page_max = ($on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;
                for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {
                    $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' :
                    '<a href="' . $base_url . "&amp;start=" . (($i - 1 ) * $per_page ) . '">' .$i . '</a>';
                    if ($i < $init_page_max + 1 ) {
                        $page_string .= ', ';
                    }
                }
                $page_string .= ($on_page < $total_pages - 4 ) ? ' ... ' : ', ';
            } else {
                $page_string .= ' ... ';
            }
            for($i = $total_pages - 2; $i < $total_pages + 1; $i++) {
                $page_string .= ($i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1 ) * $per_page ) . '">' . $i . '</a>';
                if ($i < $total_pages ) {
                    $page_string .= ", ";
                }
            }
        }
    } else {
        for($i = 1; $i < $total_pages + 1; $i++) {
            $page_string .= ($i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1 ) * $per_page ) . '">' . $i . '</a>';
            if ($i < $total_pages ) {
                $page_string .= ', ';
            }
        }
    }
    if ($add_prevnext_text ) {
        if ($on_page > 1 ) {
            $page_string = ' <a href="' . $base_url . "&amp;start=" . (($on_page - 2 ) * $per_page ) . '">Previous</a>&nbsp;&nbsp;' . $page_string;
        }
        if ($on_page < $total_pages ) {
            $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "&amp;start=" . ($on_page * $per_page ) . '">Next</a>';
        }
    }
    $page_string = 'Goto ' . $page_string;
    return $page_string;
}
?>
