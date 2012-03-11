<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * external API for mobile web services
 *
 * @package    core
 * @subpackage webservice
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Web service related functions
 */
class core_webservice_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_site_info_parameters() {
        return new external_function_parameters(
            array('serviceshortnames' => new external_multiple_structure (
                new external_value(
                    PARAM_ALPHANUMEXT,
                    'service shortname'),
                    'DEPRECATED PARAMETER - it was a design error in the original implementation. It is ignored now. (parameter kept for backward compatibility)',
                    VALUE_DEFAULT,
                    array()
                ),
            )
        );
    }

    /**
     * Return user information including profile picture + basic site information
     * Note:
     * - no capability checking because we return just known information by logged user
     * @param array $serviceshortnames - DEPRECATED PARAMETER - values will be ignored - it was an original design error, we keep for backward compatibility.
     * @return array
     */
    public function get_site_info($serviceshortnames = array()) {
        global $USER, $SITE, $CFG, $DB;

        $params = self::validate_parameters(self::get_site_info_parameters(),
                      array('serviceshortnames'=>$serviceshortnames));

        $profileimageurl = moodle_url::make_pluginfile_url(
                get_context_instance(CONTEXT_USER, $USER->id)->id, 'user', 'icon', NULL, '/', 'f1');

        //site information
        $siteinfo =  array(
            'sitename' => $SITE->fullname,
            'siteurl' => $CFG->wwwroot,
            'username' => $USER->username,
            'firstname' => $USER->firstname,
            'lastname' => $USER->lastname,
            'fullname' => fullname($USER),
            'userid' => $USER->id,
            'userpictureurl' => $profileimageurl->out(false)
        );

        //Retrieve the service and functions from the web service linked to the token
        //If you call this function directly from external (not a web service call),
        //then it will still return site info without information about a service
        //Note: wsusername/wspassword ws authentication is not supported.
        $functions = array();
        if ($CFG->enablewebservices) { //no need to check token if web service are disabled and not a ws call
            $token = optional_param('wstoken', '', PARAM_ALPHANUM);

            if (!empty($token)) { //no need to run if not a ws call
                //retrieve service shortname
                $servicesql = 'SELECT s.*
                               FROM {external_services} s, {external_tokens} t
                               WHERE t.externalserviceid = s.id AND token = ? AND t.userid = ? AND s.enabled = 1';
                $service = $DB->get_record_sql($servicesql, array($token, $USER->id));

                $siteinfo['downloadfiles'] = $service->downloadfiles;

                if (!empty($service)) {
                    //retrieve the functions
                    $functionssql = "SELECT f.*
                            FROM {external_functions} f, {external_services_functions} sf
                            WHERE f.name = sf.functionname AND sf.externalserviceid = ?";
                    $functions = $DB->get_records_sql($functionssql, array($service->id));
                } else {
                    throw new coding_exception('No service found in get_site_info: something is buggy, it should have fail at the ws server authentication layer.');
                }
            }
        }

        //built up the returned values of the list of functions
        $componentversions = array();
        $avalaiblefunctions = array();
        foreach ($functions as $function) {
            $functioninfo = array();
            $functioninfo['name'] = $function->name;
            if ($function->component == 'moodle') {
                $version = $CFG->version; //moodle version
            } else {
                $versionpath = get_component_directory($function->component).'/version.php';
                if (is_readable($versionpath)) {
                    //we store the component version once retrieved (so we don't load twice the version.php)
                    if (!isset($componentversions[$function->component])) {
                        include($versionpath);
                        $componentversions[$function->component] = $plugin->version;
                        $version = $plugin->version;
                    } else {
                        $version = $componentversions[$function->component];
                    }
                } else {
                    //function component should always have a version.php,
                    //otherwise the function should have been described with component => 'moodle'
                    throw new moodle_exception('missingversionfile', 'webservice', '', $function->component);
                }
            }
            $functioninfo['version'] = $version;
            $avalaiblefunctions[] = $functioninfo;
        }

        $siteinfo['functions'] = $avalaiblefunctions;

        return $siteinfo;
    }

    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function get_site_info_returns() {
        return new external_single_structure(
            array(
                'sitename'       => new external_value(PARAM_RAW, 'site name'),
                'username'       => new external_value(PARAM_RAW, 'username'),
                'firstname'      => new external_value(PARAM_TEXT, 'first name'),
                'lastname'       => new external_value(PARAM_TEXT, 'last name'),
                'fullname'       => new external_value(PARAM_TEXT, 'user full name'),
                'userid'         => new external_value(PARAM_INT, 'user id'),
                'siteurl'        => new external_value(PARAM_RAW, 'site url'),
                'userpictureurl' => new external_value(PARAM_URL, 'the user profile picture'),
                'functions'      => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'function name'),
                            'version' => new external_value(PARAM_FLOAT, 'The version number of moodle site/local plugin linked to the function')
                        ), 'functions that are available')
                    ),
                'downloadfiles'  => new external_value(PARAM_INT, '1 if users are allowed to download files, 0 if not', VALUE_OPTIONAL),
            )
        );
    }
}

/**
 * Deprecated web service related functions
 * @deprecated since Moodle 2.2 please use core_webservice_external instead
 */
class moodle_webservice_external extends external_api {

    /**
     * Returns description of method parameters
     * @deprecated since Moodle 2.2 please use core_webservice_external::get_site_info_parameters instead
     * @return external_function_parameters
     */
    public static function get_siteinfo_parameters() {
        return core_webservice_external::get_site_info_parameters();
    }

    /**
     * Return user information including profile picture + basic site information
     * Note:
     * - no capability checking because we return just known information by logged user
     * @deprecated since Moodle 2.2 please use core_webservice_external::get_site_info instead
     * @param array $serviceshortnames of service shortnames - the functions of these services will be returned
     * @return array
     */
    public function get_siteinfo($serviceshortnames = array()) {
        return core_webservice_external::get_site_info($serviceshortnames);
    }

    /**
     * Returns description of method result value
     * @deprecated since Moodle 2.2 please use core_webservice_external::get_site_info_returns instead
     * @return external_single_structure
     */
    public static function get_siteinfo_returns() {
        return core_webservice_external::get_site_info_returns();
    }
}