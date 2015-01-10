<?php
/**
 * DokuWiki Plugin Dropdown; ddtrigger
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 *
 * SYNTAX: 
 *    Trigger Link:
 *            [[dropdown>#pid|text]]
 *            [[dropdown>!#pid|text]]   ...dropdown disabled
 *            [[dropdown>#pid|{{image|title of image}}]]
 *
 *            <dropdown #pid> .. </dropdown>
 *
 *    Dropdown Content:
 *             <dropdown-panel #pid> ... </dropdown-panel>
 *
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_abbr_ddtrigger extends DokuWiki_Syntax_Plugin {

    protected $match_pattern = '\[\[dropdown>!?#.*?\|.*?]]';

    protected $entry_pattern = '<dropdown\b !?#.*?\>(?=.*?</dropdown>)';
    protected $exit_pattern  = '</dropdown>';

    public function getType()  { return 'formatting'; }
    public function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }
    public function getPType() { return 'normal'; }
    public function getSort()  { return 195; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->match_pattern, $mode, 'plugin_abbr_ddtrigger');
        $this->Lexer->addEntryPattern($this->entry_pattern, $mode, 'plugin_abbr_ddtrigger');
    }
    public function postConnect() {
        $this->Lexer->addExitPattern($this->exit_pattern, 'plugin_abbr_ddtrigger');
    }

    /*
     * handle syntax
     */
    public function handle($match, $state, $pos, Doku_handler $handler){
        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                $match = substr($match, 11, -2); //strip '[[dropdown>' and ']]'
                list($pid, $title) = explode('|', $match, 2);
                $pid   = trim($pid);
                $title = trim($title);
                return array($state, $pid, $title);
                
            case DOKU_LEXER_ENTER:
                $data = substr($match, 9, -1); //strip '<dropdown' and '|'
                $data = trim($data);
                return array($state, $data);

            case DOKU_LEXER_UNMATCHED:
                $handler->_addCall('cdata', array($match), $pos);
                return false;

            case DOKU_LEXER_EXIT:
                return array($state, '');
        }
        return false;
    }

    /*
     * Render output
     */
    public function render($format, Doku_renderer $renderer, $indata){

        if (empty($indata)) return false;
        list($state, $pid, $title) = $indata;

        if ($format != 'xhtml') return false;

        switch($state) {
            case DOKU_LEXER_SPECIAL:
                $class = 'plugin_dropdown_ddtrigger';
                if (($title) && preg_match('/{{(.*?)}}/', $title)) {
                    // image title
                    $html = p_render($format, p_get_instructions($title), $info);
                    $html = strip_tags($html, '<img>');
                    $ins = 'data-dropdown="'.hsc($pid).'" ';
                    $html = str_replace('<img ', '<img '.$ins, $html);
                    $html = str_replace('class="', 'class="'.$class.' ', $html);
                } else {
                    // text title
                    $html = '<span class="'.$class.'" data-dropdown="'.hsc($pid).'" title="↓dropdown">';
                    $html.= hsc($title);
                    $html.= '</span>';
                }
                $renderer->doc .= $html;
                break;

            case DOKU_LEXER_ENTER:
                $class = 'plugin_dropdown_ddtrigger';
                if ($pid[0] != '#'){
                    $pid = substr($pid, 1);
                    $class .= ' dropdown-disabled';
                }

                $renderer->doc .= '<span class="'.$class.'" data-dropdown="'.hsc($pid).'" title="↓dropdown">';
                break;

            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</span>';
                break;
        }
        return true;
    }

}

