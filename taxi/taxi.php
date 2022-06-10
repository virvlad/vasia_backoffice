<?php

    
    if (@$_GET['hotel_id']) {
        $hotel_id = intval(@$_GET['hotel_id']);
    }
    else {
        $hotel_id = 1;
    }


    $hotels = $DB->get_query_result("SELECT * FROM hotel");
    $tmpl->assign_var('hotels');


    if ($del_id = intval(@$_GET['del_id'])) {
        $DB->query("UPDATE taxi_dest SET `del`='1' WHERE id=?", $del_id);
    }


    if (isset($_GET['add_after_sort'])) {
        $hotel_id_row = $DB->get_line_result("SELECT * FROM hotel WHERE id=?", $_GET['hotel_id']);
        $add_after_sort = intval(@$_GET['add_after_sort']); 
        $next_sort = $DB->get_first_result("SELECT sort FROM taxi_dest WHERE del='0' AND hotel=? AND sort>? ORDER BY sort", $_GET['hotel_id'], $add_after_sort);
        if (!$next_sort)  {
            $next_sort = $add_after_sort + 20;
        }
        $new_sort = ($add_after_sort + $next_sort) / 2;
        $tmpl->assign_var('new_sort');
        $tmpl->assign_var('hotel_id_row');
    }


    if (@$_POST['add_dest'] && @$_POST['name']) {

        $DB->query("INSERT INTO taxi_dest 
                        (`name`, `price`, `price8`, `hotel`, `sort`) VALUES (?,?,?,?,?)",
                        $_POST['name'], $_POST['price'], $_POST['price8'], $_POST['hotel_id'], $_POST['sort']);      
    }


    if ($edit_id = intval(@$_GET['edit_id'])) {

        $toedit = $DB->get_line_result("SELECT * FROM taxi_dest WHERE id=?", $edit_id);
        $hotel_id_row = $DB->get_line_result("SELECT * FROM hotel WHERE id=?", $toedit['hotel']);
        $tmpl->assign_var('toedit');
        $tmpl->assign_var('hotel_id_row');
    }


    if (@$_POST['edit_dest'] && $_POST['name']) {
    
        $old_sort = $DB->get_first_result("SELECT sort FROM taxi_dest WHERE id=?", @$_POST['id']);
        
        if ($old_sort == @$_POST['sort']) {
            $new_sort = $old_sort;
        }
        else {
            $add_after_sort = @$_POST['sort'];
            $next_sort = $DB->get_first_result("SELECT sort FROM taxi_dest WHERE del='0' AND hotel=? AND sort>? ORDER BY sort", @$_POST['hotel_id'], $add_after_sort);
            if (!$next_sort)  {
                $next_sort = $add_after_sort + 20;
            }
            $new_sort = ($add_after_sort + $next_sort) / 2;
        }
    
        $DB->query("UPDATE taxi_dest SET 
                        `name`=?, `price`=?, `price8`=?, `hotel`=?, `sort`=? WHERE id=?",
                        $_POST['name'], $_POST['price'], $_POST['price8'], $_POST['hotel_id'], $new_sort, @$_POST['id']);
    }


    if (isset($hotel_id_row)) {$hotel_id = $hotel_id_row['id'];}

    $destinations = $DB->get_query_result("SELECT * FROM taxi_dest WHERE hotel=? AND del='0' ORDER BY sort, id", $hotel_id);
    
    $tmpl->assign_var('hotel_id');
    $tmpl->assign_var('destinations');


    $tmpl->display("/standard/adelanta/taxi.html");
