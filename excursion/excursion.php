<?php

    $PATH_IMG_DB = '/img/excursions/';
    $PATH_IMG_SRV = '/var/site/sites/studio/www' . $PATH_IMG_DB;


    if (isset($_GET['add_after_sort'])) {
        $add_after_sort = intval(@$_GET['add_after_sort']); 
        $next_sort = $DB->get_first_result("SELECT sort FROM excursion WHERE del='0' AND sort>? ORDER BY sort", $add_after_sort);
        if (!$next_sort)  {
            $next_sort = $add_after_sort + 20;
        }
        $new_sort = ($add_after_sort + $next_sort) / 2;
        $tmpl->assign_var('new_sort');
    }


    if (@$_POST['add_new_ex'] && $_POST['name']) {
        $DB->query("INSERT INTO excursion 
                        (`name`,`anons`,`descr`,`price`,`dur`,`start`,`dates`,`sort`) VALUES (?,?,?,?,?,?,?,?)",
                        $_POST['name'],$_POST['anons'],$_POST['descr'],$_POST['price'],$_POST['dur'],
                        $_POST['start'],$_POST['dates'],$_POST['sort']);
        if (@$_FILES['img']['tmp_name']) {
            $t = explode('.', strtolower(@$_FILES['img']['name']));
            $type = trim(array_pop($t));
            $last_insert_id = $DB->insert_id();
            $new_img_db_name = $PATH_IMG_DB . $last_insert_id . "." . $type; 
            $new_img = $PATH_IMG_SRV . $last_insert_id . "." . $type;
            copy(@$_FILES['img']['tmp_name'], $new_img);
            $DB->query("UPDATE excursion SET img=? WHERE id=?", $new_img_db_name, $last_insert_id);
            unlink(@$_FILES['img']['tmp_name']);
        }
    }


    if ($del_id = intval(@$_GET['del_id'])) {
        $DB->query("UPDATE excursion SET `del`='1' WHERE id=?", $del_id);
    }


    if ($edit_id = intval(@$_GET['edit_id'])) {
        $toedit = $DB->get_line_result("SELECT * FROM excursion WHERE id=?", $edit_id);
        $tmpl->assign_var('toedit');
    }


    if (@$_POST['edit_excursion'] && $_POST['name']) {
        
        $current_img = $DB->get_first_result("SELECT img FROM excursion WHERE id=?", @$_POST['id']);
        
        if (!@$_FILES['img']['tmp_name']) {
            $new_img_db_name = $current_img;
        }
        else {
            if ($current_img) {
                $t = explode('.', strtolower($current_img));
                $type = trim(array_pop($t));
                copy(($PATH_IMG_SRV . @$_POST['id'] . "." . $type),
                     ($PATH_IMG_SRV . @$_POST['id'] . "_old." . $type));
            }       
            $t = explode('.', strtolower(@$_FILES['img']['name']));
            $type = trim(array_pop($t));
            $new_img_db_name =  $PATH_IMG_DB . @$_POST['id'] . "." . $type;            
            $new_img_path    = $PATH_IMG_SRV . @$_POST['id'] . "." . $type;
            copy(@$_FILES['img']['tmp_name'], $new_img_path);
            unlink(@$_FILES['img']['tmp_name']);
        }
    
        $old_sort = $DB->get_first_result("SELECT sort FROM excursion WHERE id=?", @$_POST['id']);
        
        if ($old_sort == @$_POST['sort']) {
            $new_sort = $old_sort;
        }
        else {
            $add_after_sort = @$_POST['sort'];
            $next_sort = $DB->get_first_result("SELECT sort FROM excursion WHERE del='0' AND sort>? ORDER BY sort", $add_after_sort);
            if (!$next_sort)  {
                $next_sort = $add_after_sort + 20;
            }
            $new_sort = ($add_after_sort + $next_sort) / 2;
        }
    
        $DB->query("UPDATE excursion SET 
                        `name`=?, `anons`=?, `descr`=?, `price`=?, `dur`=?, `start`=?, `dates`=?, `img`=?, `sort`=? WHERE id=?",
                        $_POST['name'], $_POST['anons'], $_POST['descr'], $_POST['price'], $_POST['dur'],
                        $_POST['start'], $_POST['dates'], $new_img_db_name, $new_sort, @$_POST['id']);
    }


    $excursion = $DB->get_query_result("SELECT * FROM excursion WHERE del='0' ORDER BY sort");

    $tmpl->assign_var('excursion');

    $tmpl->display('/standard/adelanta/excursion.html');