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
 * A SimpleTest GroupTest that automatically finds all the
 * test files in a directory tree according to certain rules.
 *
 * @package    tool
 * @subpackage unittest
 * @copyright  &copy; 2006 The Open University
 * @author     N.D.Freear@open.ac.uk, T.J.Hunt@open.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir . '/simpletestlib/test_case.php');

/**
 * This is a composite test class for finding test cases and
 * other RunnableTest classes in a directory tree and combining
 * them into a group test.
 * @package SimpleTestEx
 */
class AutoGroupTest extends TestSuite {

    var $showsearch;

    function AutoGroupTest($showsearch, $test_name = null) {
        $this->TestSuite($test_name);
        $this->showsearch = $showsearch;
    }

    function run(&$reporter) {
        global $UNITTEST;

        $UNITTEST->running = true;
        $return = parent::run($reporter);
        unset($UNITTEST->running);
        return $return;
    }

    function setLabel($test_name) {
        //:HACK: there is no GroupTest::setLabel, so access parent::_label.
        $this->_label = $test_name;
    }

    function addIgnoreFolder($ignorefolder) {
        $this->ignorefolders[]=$ignorefolder;
    }

    function _recurseFolders($path) {
        if ($this->showsearch) {
            echo '<li>' . basename(realpath($path)) . '<ul>';
        }

        $files = scandir($path);
        static $s_count = 0;

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file_path = $path . '/' . $file;
            if (is_dir($file_path)) {
                if ($file != 'CVS' && $file != '.git' && !in_array($file_path, $this->ignorefolders)) {
                    $this->_recurseFolders($file_path);
                }
            } elseif (preg_match('/simpletest(\/|\\\\)test.*\.php$/', $file_path)) {

                $s_count++;
                // OK, found: this shows as a 'Notice' for any 'simpletest/test*.php' file.
                $this->addTestCase(new FindFileNotice($file_path, 'Found unit test file, '. $s_count));

                // addTestFile: Unfortunately this doesn't return fail/success (bool).
                $this->addTestFile($file_path, true);
            }
        }

        if ($this->showsearch) {
            echo '</ul></li>';
        }
        return $s_count;
    }

    function findTestFiles($dir) {
        if ($this->showsearch) {
            echo '<p>Searching folder: ' . realpath($dir) . '</p><ul>';
        }
        $path = $dir;
        $count = $this->_recurseFolders($path);
        if ($count <= 0) {
            $this->addTestCase(new BadAutoGroupTest($path, 'Search complete. No unit test files found'));
        } else {
            $this->addTestCase(new AutoGroupTestNotice($path, 'Search complete. Total unit test files found: '. $count));
        }
        if ($this->showsearch) {
                echo '</ul>';
        }
        return $count;
    }

    function addTestFile($file, $internalcall = false) {
        if ($this->showsearch) {
            if ($internalcall) {
                echo '<li><b>' . basename($file) . '</b></li>';
            } else {
                echo '<p>Adding test file: ' . realpath($file) . '</p>';
            }
            // Make sure that syntax errors show up suring the search, otherwise you often
            // get blank screens because evil people turn down error_reporting elsewhere.
            error_reporting(E_ALL);
        }
        if(!is_file($file) ){
            parent::addTestCase(new BadTest($file, 'Not a file or does not exist'));
        }
        parent::addTestFile($file);
    }
}


/* ======================================================================= */
// get_class_ex: Insert spaces to prettify the class-name.
function get_class_ex($object) {
    return preg_replace('/(.?)([A-Z])/', '${1} ${2}', get_class($object));
}


/**
 * A failing test base-class for when a test suite has NOT loaded properly.
 * See class, simple_test.php: BadGroupTest.
 * @package SimpleTestEx
 */
class BadTest {

    var $label;
    var $error;

    function BadTest($label, $error) {
        $this->label = $label;
        $this->error = $error;
    }

    function getLabel() {
        return $this->label;
    }

    function run(&$reporter) {
        $reporter->paintGroupStart(basename(__FILE__), $this->getSize());
        $reporter->paintFail(get_class_ex($this) .' [' . $this->getLabel() .
                '] with error [' . $this->error . ']');
        $reporter->paintGroupEnd($this->getLabel());
        return $reporter->getStatus();
    }

    /**
     * @return int the number of test cases starting.
     */
    function getSize() {
        return 0;
  }
}

/**
 * An informational notice base-class for when a test suite is being processed.
 * See class, simple_test.php: BadGroupTest.
 * @package SimpleTestEx
 */
class Notice {

    var $label;
    var $status;

    function Notice($label, $error) {
        $this->label = $label;
        $this->status = $error;
    }

    function getLabel() {
        return $this->label;
    }

    function run(&$reporter) {
        $reporter->paintGroupStart(basename(__FILE__), $this->getSize());
        $reporter->paintNotice(get_class_ex($this) .
                ' ['. $this->getLabel() .'] with status [' . $this->status . ']');
        $reporter->paintGroupEnd($this->getLabel());
        return $reporter->getStatus();
    }

    function getSize() {
        return 0;
    }
}

/**
 * A failing folder test for when the test-user specifies an invalid directory
 * (run.php?folder=woops).
 * @package SimpleTestEx
 */
class BadFolderTest extends BadTest { }

/**
 * A failing auto test for when no unit test files are found.
 * @package SimpleTestEx
 */
class BadAutoGroupTest extends BadTest { }

/**
 * Auto group test notices - 1. Search complete. 2. A test file has been found.
 * @package SimpleTestEx
 */
class AutoGroupTestNotice extends Notice { }

class FindFileNotice extends Notice { }
