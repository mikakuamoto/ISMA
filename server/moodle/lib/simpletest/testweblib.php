<?php
/**
 * Unit tests for (some of) ../weblib.php.
 *
 * @copyright &copy; 2006 The Open University
 * @author T.J.Hunt@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodlecore
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class web_test extends UnitTestCase {

    public static $includecoverage = array('lib/weblib.php');

    function setUp() {
    }

    function tearDown() {
    }

    function test_format_string() {
        global $CFG;

        // Ampersands
        $this->assertEqual(format_string("& &&&&& &&"), "&amp; &amp;&amp;&amp;&amp;&amp; &amp;&amp;");
        $this->assertEqual(format_string("ANother & &&&&& Category"), "ANother &amp; &amp;&amp;&amp;&amp;&amp; Category");
        $this->assertEqual(format_string("ANother & &&&&& Category", true), "ANother &amp; &amp;&amp;&amp;&amp;&amp; Category");
        $this->assertEqual(format_string("Nick's Test Site & Other things", true), "Nick's Test Site &amp; Other things");

        // String entities
        $this->assertEqual(format_string("&quot;"), "&quot;");

        // Digital entities
        $this->assertEqual(format_string("&11234;"), "&11234;");

        // Unicode entities
        $this->assertEqual(format_string("&#4475;"), "&#4475;");

        // < and > signs
        $originalformatstringstriptags = $CFG->formatstringstriptags;

        $CFG->formatstringstriptags = false;
        $this->assertEqual(format_string('x < 1'), 'x &lt; 1');
        $this->assertEqual(format_string('x > 1'), 'x &gt; 1');
        $this->assertEqual(format_string('x < 1 and x > 0'), 'x &lt; 1 and x &gt; 0');

        $CFG->formatstringstriptags = true;
        $this->assertEqual(format_string('x < 1'), 'x &lt; 1');
        $this->assertEqual(format_string('x > 1'), 'x &gt; 1');
        $this->assertEqual(format_string('x < 1 and x > 0'), 'x &lt; 1 and x &gt; 0');

        $CFG->formatstringstriptags = $originalformatstringstriptags;
    }

    function test_s() {
          $this->assertEqual(s("This Breaks \" Strict"), "This Breaks &quot; Strict");
          $this->assertEqual(s("This Breaks <a>\" Strict</a>"), "This Breaks &lt;a&gt;&quot; Strict&lt;/a&gt;");
    }

    function test_format_text_email() {
        $this->assertEqual("\n\nThis is a TEST",
            format_text_email('<p>This is a <strong>test</strong></p>',FORMAT_HTML));
        $this->assertEqual("\n\nThis is a TEST",
            format_text_email('<p class="frogs">This is a <strong class=\'fishes\'>test</strong></p>',FORMAT_HTML));
        $this->assertEqual('& so is this',
            format_text_email('&amp; so is this',FORMAT_HTML));
        $tl = textlib_get_instance();
        $this->assertEqual('Two bullets: '.$tl->code2utf8(8226).' *',
            format_text_email('Two bullets: &#x2022; &#8226;',FORMAT_HTML));
        $this->assertEqual($tl->code2utf8(0x7fd2).$tl->code2utf8(0x7fd2),
            format_text_email('&#x7fd2;&#x7FD2;',FORMAT_HTML));
    }

    function test_highlight() {
        $this->assertEqual(highlight('good', 'This is good'), 'This is <span class="highlight">good</span>');
        $this->assertEqual(highlight('SpaN', 'span'), '<span class="highlight">span</span>');
        $this->assertEqual(highlight('span', 'SpaN'), '<span class="highlight">SpaN</span>');
        $this->assertEqual(highlight('span', '<span>span</span>'), '<span><span class="highlight">span</span></span>');
        $this->assertEqual(highlight('good is', 'He is good'), 'He <span class="highlight">is</span> <span class="highlight">good</span>');
        $this->assertEqual(highlight('+good', 'This is good'), 'This is <span class="highlight">good</span>');
        $this->assertEqual(highlight('-good', 'This is good'), 'This is good');
        $this->assertEqual(highlight('+good', 'This is goodness'), 'This is goodness');
        $this->assertEqual(highlight('good', 'This is goodness'), 'This is <span class="highlight">good</span>ness');
    }

    function test_replace_ampersands() {
        $this->assertEqual(replace_ampersands_not_followed_by_entity("This & that &nbsp;"), "This &amp; that &nbsp;");
        $this->assertEqual(replace_ampersands_not_followed_by_entity("This &nbsp that &nbsp;"), "This &amp;nbsp that &nbsp;");
    }

    function test_strip_links() {
        $this->assertEqual(strip_links('this is a <a href="http://someaddress.com/query">link</a>'), 'this is a link');
    }

    function test_wikify_links() {
        $this->assertEqual(wikify_links('this is a <a href="http://someaddress.com/query">link</a>'), 'this is a link [ http://someaddress.com/query ]');
    }

    function test_moodle_url_round_trip() {
        $strurl = 'http://moodle.org/course/view.php?id=5';
        $url = new moodle_url($strurl);
        $this->assertEqual($strurl, $url->out(false));

        $strurl = 'http://moodle.org/user/index.php?contextid=53&sifirst=M&silast=D';
        $url = new moodle_url($strurl);
        $this->assertEqual($strurl, $url->out(false));
    }

    function test_moodle_url_round_trip_array_params() {
        $strurl = 'http://example.com/?a%5B1%5D=1&a%5B2%5D=2';
        $url = new moodle_url($strurl);
        $this->assertEqual($strurl, $url->out(false));

        $url = new moodle_url('http://example.com/?a[1]=1&a[2]=2');
        $this->assertEqual($strurl, $url->out(false));

        // For un-keyed array params, we expect 0..n keys to be returned
        $strurl = 'http://example.com/?a%5B0%5D=0&a%5B1%5D=1';
        $url = new moodle_url('http://example.com/?a[]=0&a[]=1');
        $this->assertEqual($strurl, $url->out(false));
    }

    function test_compare_url() {
        $url1 = new moodle_url('index.php', array('var1' => 1, 'var2' => 2));
        $url2 = new moodle_url('index2.php', array('var1' => 1, 'var2' => 2, 'var3' => 3));

        $this->assertFalse($url1->compare($url2, URL_MATCH_BASE));
        $this->assertFalse($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertFalse($url1->compare($url2, URL_MATCH_EXACT));

        $url2 = new moodle_url('index.php', array('var1' => 1, 'var3' => 3));

        $this->assertTrue($url1->compare($url2, URL_MATCH_BASE));
        $this->assertFalse($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertFalse($url1->compare($url2, URL_MATCH_EXACT));

        $url2 = new moodle_url('index.php', array('var1' => 1, 'var2' => 2, 'var3' => 3));

        $this->assertTrue($url1->compare($url2, URL_MATCH_BASE));
        $this->assertTrue($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertFalse($url1->compare($url2, URL_MATCH_EXACT));

        $url2 = new moodle_url('index.php', array('var2' => 2, 'var1' => 1));

        $this->assertTrue($url1->compare($url2, URL_MATCH_BASE));
        $this->assertTrue($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertTrue($url1->compare($url2, URL_MATCH_EXACT));
    }

    public function test_html_to_text_simple() {
        $this->assertEqual("\n\n_Hello_ WORLD!", html_to_text('<p><i>Hello</i> <b>world</b>!</p>'));
    }

    public function test_html_to_text_image() {
        $this->assertEqual('[edit]', html_to_text('<img src="edit.png" alt="edit" />'));
    }

    public function test_html_to_text_image_with_backslash() {
        $this->assertEqual('[\edit]', html_to_text('<img src="edit.png" alt="\edit" />'));
    }

    public function test_html_to_text_nowrap() {
        $long = "Here is a long string, more than 75 characters long, since by default html_to_text wraps text at 75 chars.";
        $this->assertEqual($long, html_to_text($long, 0));
    }

    public function test_html_to_text_dont_screw_up_utf8() {
        $this->assertEqual("\n\nAll the WORLD’S a stage.", html_to_text('<p>All the <strong>world’s</strong> a stage.</p>'));
    }

    public function test_html_to_text_trailing_whitespace() {
        $this->assertEqual('With trailing whitespace and some more text', html_to_text("With trailing whitespace   \nand some   more text", 0));
    }

    public function test_html_to_text_0() {
        $this->assertIdentical('0', html_to_text('0'));
    }

    public function test_html_to_text_pre_parsing_problem() {
        $strorig = 'Consider the following function:<br /><pre><span style="color: rgb(153, 51, 102);">void FillMeUp(char* in_string) {'.
                   '<br />  int i = 0;<br />  while (in_string[i] != \'\0\') {<br />    in_string[i] = \'X\';<br />    i++;<br />  }<br />'.
                   '}</span></pre>What would happen if a non-terminated string were input to this function?<br /><br />';

        $strconv = 'Consider the following function:

void FillMeUp(char* in_string) {
 int i = 0;
 while (in_string[i] != \'\0\') {
 in_string[i] = \'X\';
 i++;
 }
}
What would happen if a non-terminated string were input to this function?

';

        $this->assertIdentical($strconv, html_to_text($strorig));
    }

    public function test_clean_text() {
        $text = "lala <applet>xx</applet>";
        $this->assertEqual($text, clean_text($text, FORMAT_PLAIN));
        $this->assertEqual('lala xx', clean_text($text, FORMAT_MARKDOWN));
        $this->assertEqual('lala xx', clean_text($text, FORMAT_MOODLE));
        $this->assertEqual('lala xx', clean_text($text, FORMAT_HTML));
    }
}
