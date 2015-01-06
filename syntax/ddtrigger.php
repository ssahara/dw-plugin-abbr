<?php
/**
 * DokuWiki Plugin Dropdown; ddtrigger
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 *
 * SYNTAX: 
 *    Trigger Link:     {{dropdown #pid|text}}
 *    Dropdown Content:  <dropdown-panel #pid> ... </dropdown-panel>
 *
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_abbr_ddtrigger extends DokuWiki_Syntax_Plugin {

    protected $match_pattern1 = '{{dropdown\b[^\n}]+?\}\}\}\}';
    protected $match_pattern2 = '{{dropdown\b.*?\|.*?}}';

    public function getType()  { return 'substition'; }
    public function getPType() { return 'normal'; }
    public function getSort()  { return 305; }
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->match_pattern2, $mode, 'plugin_abbr_ddtrigger');
    }

    /*
     * handle syntax
     */
    public function handle($match, $state, $pos, Doku_handler $handler){

        $match = substr($match, 10, -2); // strip '{{dropdown' and '}}'
        list($params, $title) = explode('|', $match, 2);
        
        $id = trim($params);  // eg. #dropdown-1
        
        return array($state, $id, $title);
    }

    /*
     * Render output
     */
    public function render($format, Doku_renderer $renderer, $data){

        if ($format != 'xhtml') return false;

        list($state, $id, $title) = $data;

        // 例 <a href="#" data-dropdown="#dropdown-1">dropdown</a>
        $html = '<a href="#" data-dropdown="'.$id.'">'.$title.'</a>';
        $renderer->doc .= $html;
        return true;
    }

}

