<?php
/**
 * DokuWiki Plugin Abbr: abbr tag for abbreviation in Wiki text
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 *
  SYNTAX:
        <abbr title="description">TARGET</abbr>

  OUTPUT:
        <abbr title="description">TARGET</abbr>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_abbr_htmltag extends DokuWiki_Syntax_Plugin {

    protected $entry_pattern    = '<abbr\b(?:\s+title=.*?)>(?=.*?</abbr>)';
    protected $exit_pattern     = '</abbr>';

    public function getType() { return 'formatting'; }
    public function getSort() { return 305; }

    public function connectTo($mode) {
        $this->Lexer->addEntryPattern($this->entry_pattern,$mode,
            implode('_', array('plugin',$this->getPluginName(),$this->getPluginComponent(),))
        );
    }
    public function postConnect() {
        $this->Lexer->addExitPattern($this->exit_pattern,
            implode('_', array('plugin',$this->getPluginName(),$this->getPluginComponent(),))
        );
    }

   /**
    * Handle the match
    */
    public function handle($match, $state, $pos, Doku_Handler $handler) {

        switch ($state) {
            case DOKU_LEXER_ENTER :
            case DOKU_LEXER_UNMATCHED :
            case DOKU_LEXER_EXIT :
                return array($state, $match);
                break;
        }
        return false;
    }

   /**
    * Create output
    */
    public function render($format, Doku_Renderer $renderer, $data) {

        if($format == 'xhtml') {
            list($state, $match) = $data;
            $match = $data[1];

            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $renderer->doc .= $match;
                    break;
            //  case DOKU_LEXER_MATCHED :
            //      break;
                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $renderer->_xmlEntities($match);
                    break;
                case DOKU_LEXER_EXIT :
                    $renderer->doc .= $match;
                    break;
            //  case DOKU_LEXER_SPECIAL :
            //      break;
            }
            return true;
        }
        return false;
    }
}
