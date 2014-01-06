<?php
/**
 * DokuWiki Plugin Abbr: abbr tag for abbreviation in Wiki text
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 *
  SYNTAX:
        <abbr>whole phrase (shortened word)</abbr>
        <abbr>whole phrase （shortened word）</abbr>  // Japanese: 全角カッコ
        <abbr>shortened word|whole phrase</abbr>
        <abbr short>whole phrase (shortened word)</abbr>

  OUTPUT:
        <abbr title="whole phrase">shortened word</abbr>
        <abbr>shortened word</abbr>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_abbr_abbreviation extends DokuWiki_Syntax_Plugin {

    public function getType() { return 'formatting'; }
    public function getSort() { return 65; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<abbr(?: short)?>.*?</abbr>',$mode,'plugin_abbr_abbreviation');
    }

   /**
    * Handle the match
    */
    public function handle($match, $state, $pos, &$handler) {

        $match = substr($match, 6,-7); // drop markup
        if (preg_match("/^short>/", $match)) {
            $shortonly = true;
            $match = substr($match, 6);
        } else {
            $shortonly = false;
        }

        if (preg_match("/(^.*)（(((?>[^（）]+)|(?R))*)）$/u", $match, $matches)) {
            // Japanese 末尾に置いた「全角カッコ内」で省略語を指定 （日本語向け）
            $shortened = $matches[2];
            $phrase = $matches[1];
        } elseif (preg_match("/(^.*)\((((?>[^()]+)|(?R))*)\)$/", $match, $matches)) {
            // shortened word or phrase will found in tailing ()
            $shortened = $matches[2];
            $phrase = $matches[1];
        } elseif (strpos($match,'|') !== false) {
            list($shortened, $phrase) = explode('|',$match,2);
            $shortened = trim($shortened);
            $phrase = trim($phrase);
        } else {
            //msg('shortend word not found in "'.$match.'"' ,2);
            $shortened = trim($match);
            $phrase = $shortened;
        }
        return array($state, $shortonly, $shortened, $phrase);
    }

   /**
    * Create output
    */
    public function render($mode, &$renderer, $data) {

        if ($mode != 'xhtml') return false;

        list($state, $shortonly, $shortened, $phrase) = $data;
        if ($shortonly) {
            //$html = '<abbr>'.hsc($shortened).'</abbr>';
            $html = hsc($shortened);
        } else {
            $html = '<abbr';
            $html.= (empty($phrase)) ? '>' : ' title="'.hsc($phrase).'">';
            $html.= hsc($shortened).'</abbr>';
        }
        $renderer->doc .= $html;
        return true;
    }
}
