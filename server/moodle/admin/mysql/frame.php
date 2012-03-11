<?PHP

    require("../../config.php");

    // no debug messages before the meta header
    if ($CFG->debug > E_NOTICE) {
        $CFG->debug = E_NOTICE;
        error_reporting($CFG->debug);
    }

    require_login();


    if ($site = get_site()) {
        if (function_exists('require_capability')) {
            require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
        } else if (!isadmin()) {
            error("You need to be admin to use this page");
        }
    }

    $stradmin = get_string("administration");
    $strmanagedatabase = get_string("managedatabase");

    if (!empty($_GET['top'])) {
        if ($CFG->version > 2009080100) {    /// Moodle 2.0 dev and new page stuff
            $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
            $PAGE->set_url('/admin/mysql/frame.php');
            $PAGE->set_pagelayout('frametop');
            $PAGE->set_title($site->fullname. ': '. $strmanagedatabase);
            $PAGE->set_heading($site->fullname);
            $PAGE->set_headingmenu(user_login_string($site, $USER));
            // Adding these manually now until the navigation recognises this page automatically  TODO MDL-14632
            $PAGE->navbar->add($stradmin, new moodle_url($CFG->wwwroot.'/admin/index.php'));
            $PAGE->navbar->add($strmanagedatabase);
            echo $OUTPUT->header();
            echo $OUTPUT->footer();

        } else {
            print_header("$site->shortname: $strmanagedatabase", "$site->fullname",
                         "<a target=_parent href=\"../index.php\">$stradmin</a> -> $strmanagedatabase");
        }
    } else {
        if (function_exists('current_charset')) {
            $charset = current_charset();
        } else {
            //older Moodle versions
            $charset = get_string('thischarset');
        }
        echo "<head><title>$site->shortname: $strmanagedatabase</title></head>\n";
        echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$charset\" />";
        echo "<frameset rows=70,*>";
        echo "<frame src=\"frame.php?top=1\">";
        echo "<frame src=\"index.php\">";
        echo "</frameset>";
    }

?>
