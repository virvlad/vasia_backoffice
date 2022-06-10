<?php

    $PATH_IMG_SRV = '/var/site/sites/studio/www/img/rs/';


    if (@$add_after_sort = intval($_GET['add_after_sort'])) {
            $next_sort = $DB->get_first_result("SELECT sort FROM vg.rs_subsections WHERE sort>? ORDER BY sort", $add_after_sort);
            if ((!$next_sort) OR (intval($next_sort/1000) > intval($add_after_sort/1000))) {
                $next_sort = (intval($add_after_sort/1000) + 1) * 1000;
            }
            $new_sort = ($add_after_sort + $next_sort) / 2;
            $tmpl->assign_var('new_sort');
    }

    if (@$_POST['add_sub_sect'] && $_POST['name']) {
            $DB->query("INSERT INTO vg.rs_subsections (`name`,`descr`,`section`,`sort`) VALUES (?,?,?,?)",
                                $_POST['name'], $_POST['descr'], $_POST['section_id'], $_POST['sort']);
    }

    if ($del_id = intval(@$_GET['del_id'])) {
            $DB->query("UPDATE vg.rs_subsections SET `del`='1' WHERE id=?", $del_id);
    }

    if (@$_POST['edit_sub_sect'] && $_POST['name']) {

            $old_sort_subsection = $DB->get_first_result("SELECT sort FROM vg.rs_subsections WHERE id=?", @$_POST['id']);
            if ($old_sort_subsection == @$_POST['sort']) {
                $new_sort_subsection = $old_sort_subsection;
            }
            else {
                $add_after_sort = @$_POST['sort'];
                $next_sort = $DB->get_first_result("SELECT sort FROM vg.rs_subsections WHERE sort>? ORDER BY sort", $add_after_sort);
                if ((!$next_sort) OR (intval($next_sort/1000) > intval($add_after_sort/1000))) {
                    $next_sort = (intval($add_after_sort/1000) + 1) * 1000;
                }
                $new_sort_subsection = ($add_after_sort + $next_sort) / 2;
            }
            $DB->query("UPDATE vg.rs_subsections SET `name`=?, `descr`=?, `sort`=?, `section`=? WHERE id=?",
                    $_POST['name'], $_POST['descr'], $new_sort_subsection, $_POST['section_id'],$_POST['id']);
    }

        /* echo 'Edit subsection process <br><br>';

            $old_sort_subsection_row = $DB->get_line_result("SELECT sort FROM vg.rs_subsections WHERE id=?", $_POST['id']);
            echo '$old_sort_subsection_row   '; var_dump ($old_sort_subsection_row); echo '<br><br>';

            $old_sort_subsection = $old_sort_subsection_row['sort'];
            echo '$old_sort_subsection   '; var_dump ($old_sort_subsection); echo '<br><br>';

            $shift_array = $DB->get_query_result("SELECT * FROM vg.rs_subsections WHERE sort>=? AND sort<=? ORDER BY sort", $old_sort_subsection, $_POST['sort']);
            echo '$shift_array   '; var_dump ($shift_array); echo '<br><br>';

            $shift_array_sort = $DB->get_query_result("SELECT sort FROM vg.rs_subsections WHERE sort>=? AND sort<=? ORDER BY sort", $old_sort_subsection, $_POST['sort']);
            echo '$shift_array_sort   '; var_dump ($shift_array_sort); echo '<br><br>';

            print_r ($shift_array_sort); exit ();
            $len = count($shift_array_sort);
            echo '$len   '; var_dump ($len); echo '<br><br>';

            foreach ($shift_array_sort as $key => $sort_current) {
                echo '           $key   '; var_dump ($key); echo '<br>';

                if ($key == $len) {
                    echo '$key == $len' . $key . ' == ' . $len;
                    /* $DB->query("UPDATE vg.rs_subsections SET sort=? WHERE id=?", $_POST['sort'], $_POST['id']);
                }
                else {
                    echo '$sort_current   '; var_dump ($sort_current); echo '     sort      '; var_dump ($sort); echo '       (int)$sort_current["sort"]   '; var_dump ((int)$sort_current["sort"]); echo '  <br>    ';
                    /* exit ($shift_array_sort[$key+1]);
                    var_dump ($sort_current); echo ' <br>    ';
                    var_dump ($sort_current["sort"]); echo '  <br>    ';
                    var_dump ((int)$sort_current["sort"]); echo '  <br>    ';
                    var_dump ($shift_array_sort[$key+1]); echo '     '; exit ();
                    $next_sort_current = $shift_array_sort[$key+1];
                    var_dump ($shift_array_sort[$key+1]); echo '     ';
                    var_dump ($next_sort_current); echo '  <br>    ';
                    var_dump ((int)$next_sort_current["sort"]);
                    var_dump ($sort_current);
                    var_dump ((int)$sort_current["sort"]); echo '  <br>    ';
                    $DB->query("UPDATE vg.rs_subsections SET sort=? WHERE sort=?", (int)$sort_current["sort"], (int)$next_sort_current["sort"]);
                }
                echo ' <br> ';
            } */


    if ($add_item_subsect = intval(@$_GET['add_item_subsect'])) {
            $tmpl->assign_var('add_item_subsect');
            $subsection_items = $DB->get_query_result("SELECT * FROM vg.rs_items WHERE del='0' AND subsection=?", $add_item_subsect);
            $tmpl->assign_var('subsection_items');
    }

    if (@$_POST['add_new_item'] && $_POST['name']) {
        
        if (@$_POST['vegantype']=="1") {$vegan_flag="1";} else {$vegan_flag="0";}
        
        $DB->query("INSERT INTO vg.rs_items (`subsection`,`name`,`descr`,`price`,`vegantype`) VALUES (?,?,?,?,?)",
                $_POST['subsection'], $_POST['name'], $_POST['descr'], $_POST['price'], $vegan_flag);
        
        if (@$_FILES['pic']['tmp_name']) {
                $t = explode('.', strtolower(@$_FILES['pic']['name']));
                $type = trim(array_pop($t));
                $last_insert_id = $DB->insert_id();
                $new_img_name = $last_insert_id . "." . $type;
                copy(@$_FILES['pic']['tmp_name'], $PATH_IMG_SRV . $new_img_name);
                $DB->query("UPDATE vg.rs_items SET pic=? WHERE id=?", $new_img_name, $last_insert_id);
                unlink(@$_FILES['pic']['tmp_name']);
        }
    }

    if ($del_item_id = intval(@$_GET['del_item_id'])) {
        $DB->query("UPDATE vg.rs_items SET `del`='1' WHERE id=?", $del_item_id);
        $item_id_row = $DB->get_line_result("SELECT * FROM vg.rs_items WHERE id=?", $del_item_id);
    }

    if ($edit_item_id = intval(@$_GET['edit_item_id'])) {
        $toedit_item = $DB->get_line_result("SELECT * FROM vg.rs_items WHERE id=?", $edit_item_id);
        $subsection_items = $DB->get_query_result("SELECT * FROM vg.rs_items WHERE del='0' AND subsection=?", $toedit_item['subsection']);
        $tmpl->assign_var('toedit_item');
        $tmpl->assign_var('subsection_items');
    }

    if (@$_POST['edit_item'] && $_POST['name']) {
        $current_pic = $DB->get_first_result("SELECT pic FROM vg.rs_items WHERE id=?", @$_POST['id']);
        
        if (!@$_FILES['pic']['tmp_name']) {
                $new_img_name = $current_pic;
        }
        else {
            if ($current_pic) {
                $t = explode('.', strtolower(@$_FILES['pic']['name']));
                $type = trim(array_pop($t));
                /* $old_file_name = @$_POST['id'] . "_" . date("ymdhis") . "." . $type; */
                copy(($PATH_IMG_SRV . $current_pic), 
                     ($PATH_IMG_SRV . @$_POST['id'] . "_old" . "." . $type));
            }   
            $t = explode('.', strtolower(@$_FILES['pic']['name']));
            $type = trim(array_pop($t));
            $new_img_name = @$_POST['id'] . "." . $type;
            copy(@$_FILES['pic']['tmp_name'], $PATH_IMG_SRV . $new_img_name);
            unlink(@$_FILES['pic']['tmp_name']);
        }
        
        $DB->query("UPDATE vg.rs_items SET `name`=?, `descr`=?, `price`=?, `vegantype`=?, `pic`=? WHERE id=?",
            $_POST['name'], $_POST['descr'], $_POST['price'], $_POST['vegantype'], $new_img_name, $_POST['id']);
        
        $item_id_row = $DB->get_line_result("SELECT * FROM vg.rs_items WHERE id=?", $_POST['id']);
    }

    if (($edit_id = intval(@$_GET['edit_id']))
        OR ($edit_id = intval(@$item_id_row['subsection']))
        OR ($edit_id = intval(@$_POST['subsection']))) {
        $toedit = $DB->get_line_result("SELECT * FROM vg.rs_subsections WHERE id=?", $edit_id);
        $subsection_items = $DB->get_query_result("SELECT * FROM vg.rs_items WHERE del='0' AND subsection=?", $edit_id);
        $subsections_current_section = $DB->get_query_result("SELECT * FROM vg.rs_subsections WHERE del='0' AND section=? ORDER BY sort", $toedit['section']);
        $tmpl->assign_var('toedit');
        $tmpl->assign_var('subsection_items');
        $tmpl->assign_var('subsections_current_section');
    }

    $sections = $DB->get_query_result("SELECT * FROM vg.rs_sections WHERE 1");
    $subsections = $DB->get_query_result("SELECT * FROM vg.rs_subsections WHERE del='0' ORDER BY sort");

    $tmpl->assign_var('sections');
    $tmpl->assign_var('subsections');
    
    $tmpl->display('/standard/adelanta/roomservice/index.html');
