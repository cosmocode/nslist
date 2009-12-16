<?php
/**
 * Info Plugin: Displays information about various DokuWiki internals
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */


if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/search.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_list extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => '2006-01-11',
            'name'   => 'List Plugin',
            'desc'   => 'Lists all pages in a given namespace',
            'url'    => 'http://wiki.splitbrain.org/plugin:list',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }
   
    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */ 
    function getSort(){
        return 302;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{list>[^}]+}}',$mode,'plugin_list');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        $match = substr($match,7,-2); //strip {{list> from start and }} from end

        // get title if given
        list($ns,$title) = explode('|',$match);
        $title = trim($title);

        // get alignment
        if(preg_match('/^(\t|  ).*(\t|  )$/',$ns)){
            $align = ' mediacenter'; //FIXME doesn't work
        }elseif(preg_match('/^(\t|  )/',$ns)){
            $align = ' mediaright';
        }elseif(preg_match('/(\t|  )$/',$ns)){
            $align = ' medialeft';
        }else{
            $align = '';
        }

        // prepare data
        return array( 'ns'    => str_replace(':','/',cleanID($ns)),
                      'title' => $title,
                      'align' => $align,  
                    );
    }

    /**
     * Create output
     */
    function render($format, &$renderer, $data) {
        global $conf;
        global $lang;
        if($format != 'xhtml') return false;

        // read the directory
        $result = array();
        search(&$result,$conf['datadir'],'search_list','',$data['ns']);

        $renderer->doc .= '<div class="listplugin'.$data['align'].'">';

        if($data['title']){
            $renderer->doc .= '<div>'.htmlspecialchars($data['title']).'</div>';
        }

        if(!count($result)){
            $renderer->doc .= '<span>'.$lang['nothingfound'].'</span>';
        }else{
            $renderer->doc .= '<ul>';
            $renderer->doc .= html_buildlist($result,'','html_list_index');
            $renderer->doc .= '</ul>';
        }

        $renderer->doc .= '</div>';
        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
