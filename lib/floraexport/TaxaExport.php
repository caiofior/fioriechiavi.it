<?php
namespace floraexport;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Taka export to text class
 *
 * @author caiofior
 */
class TaxaExport {
    /**
     * Mark down constant
     */
    const MD='md';
    /**
     * HTML constant
     */
    const HTML='html';
    /**
     * PDF constant
     */
    const PDF='pdf';
    /**
     * DOC constant
     */
    const DOC='doc';
     /**
     * ODT constant
     */
    const ODT='odt';
     /**
     * EPUB constant
     */
    const EPUB='epub';
    /**
     * Database adapter
     * @var \Zend\Db\Adapter\Adapter
     */
    private $db;
    /**
     * Root taxa id
     * @var int
     */
    private $rootId;
    /**
     * Instantiates the class
     * @param \Zend\Db\Adapter\Adapter $db
     * @param int $rootId
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db,$rootId) {
        $this->db = $db;
        $this->rootId = $rootId;
    }
    /**
     * Exports the data
     * @param resource $stream
     * @param string $format
     */
    public function export($streamOutput,$format) {
        if ($format != self::MD) {
            $pandocExists = shell_exec('which pandoc');
            $pandocExists = ($pandocExists!='');
            if (!$pandocExists) {
                throw new \Exception('missing pandoc command',1509221457);
            }
            $tempFileName = $GLOBALS['sessionDir'].DIRECTORY_SEPARATOR.uniqid();
            $stream = fopen($tempFileName, 'w');
        } else {
            $stream = $streamOutput;
        }
        $taxaToDo=array($this->rootId);
        $taxaDone=array();
        $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
        if ($this->rootId == 1) {
            fwrite($stream, '#1 Flora d\'Italia - Chiave d\'insieme'.PHP_EOL.PHP_EOL);
        } else {
            $taxa->loadFromId($this->rootId);
            fwrite($stream, '#1 Flora d\'Italia - Chiave '.$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').PHP_EOL.PHP_EOL);
        }
        while (sizeof($taxaToDo)>0) {
            $taxaId = array_shift($taxaToDo);
            if(array_key_exists($taxaId, $taxaDone)) {
                continue;
            }
            $taxaDone[$taxaId]=$taxa->getData('name');
            $taxa->loadFromId($taxaId);
            $taxaParentColl =  $taxa->getParentColl()->getItems();
            if ($taxa->getData('id') != 1) { 
                fwrite($stream, str_repeat('#', count($taxaParentColl)+1).' ');
                fwrite($stream, $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').' {#t'.$taxa->getData('id').'}'.PHP_EOL.PHP_EOL);
            }
            if ($taxa->getData('id') != $this->rootId) {
                end($taxaParentColl);
                $parentTaxa = current($taxaParentColl);
                fwrite($stream,'['.$parentTaxa->getRawData('taxa_kind_initials').' '.$parentTaxa->getData('name').'](#t'.$parentTaxa->getData('id').')'.PHP_EOL.PHP_EOL);
            }
            if ($taxa->getData('description') != '') {
                fwrite($stream, $taxa->getData('description').PHP_EOL.PHP_EOL);
            }
            
            $attributeColl = $taxa->getTaxaAttributeColl();
            if ($attributeColl->count()>0) {
                foreach($attributeColl->getItems() as $attribute) {
                    fwrite($stream,'**'.$attribute->getData('name').'**'.PHP_EOL);
                    fwrite($stream,':   '.$attribute->getRawData('value').PHP_EOL);
                }
                fwrite($stream,PHP_EOL);
            }
            
            $imageColl = $taxa->getTaxaImageColl();
            if ($imageColl->count() > 0) {
                foreach($imageColl->getItems() as $key=>$image) {
                    fwrite($stream,'![]('.$image->getPath().')'.PHP_EOL);
                }
                fwrite($stream,PHP_EOL);
            }
            
            $dicoItemColl = $taxa->getDicoItemColl();
            $childrensIds=array();
            $positions = array();
            $lastPosition = 0;
            if ($dicoItemColl->count() >0) {
                foreach ($dicoItemColl->getItems() as $dicoItem) {
                    if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getData('taxa_id') != 0) {
                        array_push($childrensIds, $dicoItem->getData('taxa_id')); 
                    }
                    if ($dicoItem->getData('text') != '') {
                        $lastCharacter = substr($dicoItem->getData('id'),-1);
                        if ($lastCharacter == 0) {
                           $lastPosition++;
                           $positions[substr($dicoItem->getData('id'),0,-1).'0']= $lastPosition;
                           $positions[substr($dicoItem->getData('id'),0,-1).'1']= $lastPosition;
                        }
                        fwrite($stream,'* '.str_repeat('&#160;', (strlen($dicoItem->getData('id')))));
                        fwrite($stream,$positions[$dicoItem->getData('id')].' '.$dicoItem->getData('text'));
                        if ($dicoItem->getData('taxa_id')!= '') {
                            fwrite($stream, ' ['.$dicoItem->getRawData('initials').' '.$dicoItem->getRawData('name').'](#t'.$dicoItem->getData('taxa_id').')');
                        }
                        fwrite($stream,PHP_EOL);
                    }
                }
                fwrite($stream,PHP_EOL);
                $taxaToDo = array_merge($childrensIds,$taxaToDo);
            }
        }
        if(count($taxaDone)>0) {
            asort($taxaDone);
            foreach($taxaDone as $taxaId => $taxaName) {
                $taxa->loadFromId($taxaId);
                fwrite($stream,'* ['.$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').'](#t'.$taxa->getData('id').')'.PHP_EOL);
            }
        }
        switch ($format) {
                case 'epub':
                case 'odt':
                case 'doc':
                case 'pdf':
                case 'html':
                    $cmd = 'pandoc -f markdown+definition_lists --latex-engine=xelatex -s -o '.$tempFileName.'.'.$format.' '.$tempFileName.' 2>&1';
                    $error = shell_exec($cmd);
                    unlink($tempFileName);
                    if (!is_file($tempFileName.'.'.$format)) {
                        throw new \Exception('Error on generating pandoc output with command '.$cmd.' '.$error,1509221630);
                    }
                    $tmpStream = fopen($tempFileName.'.'.$format,'r');
                    fwrite($streamOutput,fread($tmpStream,  filesize($tempFileName.'.'.$format)));
                    fclose($tmpStream);
                    unlink($tempFileName.'.'.$format);
                break;
        }
    }
}