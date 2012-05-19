<?php

/**
 * Bloco My Events ISMA  
 * Bloco e Funções
 *
 * @package	block
 * @subpackage  my_events_isma
 * @author	Mika Kuamoto - Paulo Silveira
 */

class block_my_events_isma extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_my_events_isma');
    }

    function get_content() {
        global $USER, $CFG, $SESSION;
        $cal_m = optional_param( 'cal_m', 0, PARAM_INT );
        $cal_y = optional_param( 'cal_y', 0, PARAM_INT );

        require_once($CFG->dirroot.'/calendar/lib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->text = '';

        $filtercourse    = array();
        if (empty($this->instance)) { 
            $courseshown = false;
            $this->content->footer = '';

        } else {
            $courseshown = $this->page->course->id;
            $this->content->footer = '<div class="gotocal"><a href="'.$CFG->wwwroot.
                                     '/calendar/view.php?view=upcoming&amp;course='.$courseshown.'">'.
                                      get_string('gotocalendar', 'calendar').'</a>...</div>';
            $context = get_context_instance(CONTEXT_COURSE, $courseshown);
            if (has_any_capability(array('moodle/calendar:manageentries', 'moodle/calendar:manageownentries'), $context)) {
                $this->content->footer .= '<div class="newevent"><a href="'.$CFG->wwwroot.
                                          '/calendar/event.php?action=new&amp;course='.$courseshown.'">'.
                                           get_string('newevent', 'calendar').'</a>...</div>';
            }
            if ($courseshown == SITEID) {
                $filtercourse = calendar_get_default_courses();
            } else {
                $filtercourse = array($courseshown => $this->page->course);
            }
        }

        list($courses, $group, $user) = calendar_set_filters($filtercourse);

        $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
        if (isset($CFG->calendar_lookahead)) {
            $defaultlookahead = intval($CFG->calendar_lookahead);
        }
        $lookahead = get_user_preferences('calendar_lookahead', $defaultlookahead);

        $defaultmaxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;
        if (isset($CFG->calendar_maxevents)) {
            $defaultmaxevents = intval($CFG->calendar_maxevents);
        }
        $maxevents = get_user_preferences('calendar_maxevents', $defaultmaxevents);
        $events = calendar_get_upcoming($courses, $group, $user, $lookahead, $maxevents);
        
        $important = array();
        foreach($events as $event){
            if(substr($event->name, 0, 4) != 'Aula'){
                $important[] = $event;
            }
        }
        
        if (!empty($this->instance)) {
            $this->config->eventview = 'all';
            if ($this->config->eventview == 'all'){
                $this->content->text .= $this->block_my_events_isma_get_calendar($events, 'view.php?view=day&amp;course='.$courseshown.'&amp;');
            } else {
                $this->content->text .= $this->block_my_events_isma_get_calendar($important, 'view.php?view=day&amp;course='.$courseshown.'&amp;');
            }
        }

        if (empty($this->content->text)) {
            $this->content->text = '<div class="post">'. get_string('noupcomingevents', 'calendar').'</div>';
        }

        return $this->content;
    }
    
    function block_my_events_isma_get_calendar($events, $linkhref = NULL) {
        $content = '';
        $lines = count($events);
        if (!$lines) {
            return $content;
        }

        for ($i = 0; $i < $lines; ++$i) {
            if (!isset($events[$i]->time)) {   
                continue;
            }
            $events[$i] = $this->block_my_events_isma_add_event_metadata($events[$i]);
            $content .= '<div class="event"><span class="icon c0">'.$events[$i]->icon.'</span> ';
            if (!empty($events[$i]->referer)) {
                $content .= $events[$i]->referer;
            } else {
                if(!empty($linkhref)) {
                    $ed = usergetdate($events[$i]->timestart);
                    $href = calendar_get_link_href(new moodle_url(CALENDAR_URL.$linkhref), $ed['mday'], $ed['mon'], $ed['year']);
                    $href->set_anchor('event_'.$events[$i]->id);
                    $content .= html_writer::link($href, $events[$i]->name);
                }
                else {
                    $content .= $events[$i]->name;
                }
            }
            $events[$i]->time = str_replace('&raquo;', '<br />&raquo;', $events[$i]->time);
            $content .= '<div class="date">'.$events[$i]->time.'</div></div>';
            if ($i < $lines - 1) $content .= '<hr />';
        }

        return $content;
    }

    function block_my_events_isma_add_event_metadata($event) {
        global $CFG, $OUTPUT;
      
        $event->name = format_string($event->name,true);

        if(!empty($event->modulename)) {    
            $module = calendar_get_module_cached($coursecache, $event->modulename, $event->instance);

            if ($module === false) {
                return;
            }

            $modulename = get_string('modulename', $event->modulename);
            if (get_string_manager()->string_exists($event->eventtype, $event->modulename)) {
                $eventtype = get_string($event->eventtype, $event->modulename);
            } else {
                $eventtype = '';
            }
            $icon = $OUTPUT->pix_url('icon', $event->modulename) . '';

            $context = get_context_instance(CONTEXT_COURSE, $module->course);
            $fullname = format_string($coursecache[$module->course]->fullname, true, array('context' => $context));

            $event->icon = '<img height="16" width="16" src="'.$icon.'" alt="'.$eventtype.'" title="'.$modulename.'" style="vertical-align: middle;" />';
            $event->referer = '<a href="'.$CFG->wwwroot.'/mod/'.$event->modulename.'/view.php?id='.$module->id.'">'.$event->name.'</a>';
            $event->courselink = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$module->course.'">'.$fullname.'</a>';
            $event->cmid = $module->id;


        } else if($event->courseid == SITEID) {                              
            $event->icon = '<img height="16" width="16" src="'.$OUTPUT->pix_url('c/site') . '" alt="'.get_string('globalevent', 'calendar').'" style="vertical-align: middle;" />';
            $event->cssclass = 'calendar_event_global';
        } else if($event->courseid != 0 && $event->courseid != SITEID && $event->groupid == 0) {          
            calendar_get_course_cached($coursecache, $event->courseid);

            $context = get_context_instance(CONTEXT_COURSE, $event->courseid);
            $fullname = format_string($coursecache[$event->courseid]->fullname, true, array('context' => $context));

            if(substr($event->name, 0, 4) == 'Aula') {
                $event->icon = '<img height="16" width="16" src="'.$CFG->wwwroot . '/blocks/my_events_isma/pix/class.gif" alt="'.get_string('courseevent', 'calendar').'" style="vertical-align: middle;" />';
            } else {
                $event->icon = '<img height="16" width="16" src="'.$CFG->wwwroot . '/blocks/my_events_isma/pix/warning.gif" alt="'.get_string('courseevent', 'calendar').'" style="vertical-align: middle;" />';
            }
            $event->courselink = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$event->courseid.'">'.$fullname.'</a>';
            $event->cssclass = 'calendar_event_course';
        } else if ($event->groupid) {                                    
            $event->icon = '<img height="16" width="16" src="'.$OUTPUT->pix_url('c/group') . '" alt="'.get_string('groupevent', 'calendar').'" style="vertical-align: middle;" />';
            $event->cssclass = 'calendar_event_group';
        } else if($event->userid) {                                      
            $event->icon = '<img height="16" width="16" src="'.$OUTPUT->pix_url('c/user') . '" alt="'.get_string('userevent', 'calendar').'" style="vertical-align: middle;" />';
            $event->cssclass = 'calendar_event_user';
        }
        return $event;
    }
}


