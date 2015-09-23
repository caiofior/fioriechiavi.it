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
     * HTML5 constant
     */
    const HTML5='html5';
    /**
     * PDF constant
     */
    const PDF='pdf';
    /**
     * DOC constant
     */
    const DOC='doc';
    /**
     * DOCX constant
     */
    const DOCX='docx';
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
        $image = new \flora\taxa\TaxaImage($this->db);
        $basePath = $this->db->baseDir;
        $oClass = new \ReflectionClass(__CLASS__);
        if (!in_array($format, $oClass->getConstants())) {
            throw new \Exception('Export format '.$format.' not available',1509231547);
        }
        unset($oClass);
        if ($format != self::MD) {
            if ( ($format == self::HTML || $format == self::HTML5) && $this->db->config->externalUrl != '') {
                $basePath = $this->db->config->externalUrl;    
            }
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
        $count = 0;
        while (sizeof($taxaToDo)>0) {
            $taxaId = array_shift($taxaToDo);
            if(array_key_exists($taxaId, $taxaDone)) {
                continue;
            }
            $taxa->loadFromId($taxaId);
            $taxaDone[$taxaId]=$taxa->getData('name');
            if ($count++%10 == 0) {
                file_put_contents('php://stderr', $count."\t".$taxa->getData('name').str_repeat(' ', 20)."\r");
            }
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
                    switch ($attribute->getData('name')) {
                        case 'Inizio fioritura' :
                        break;
                        case 'Fine fioritura' :
                        break;
                        case 'Limite altitudinale inferiore' :
                        break;
                        case 'Limite altitudinale superiore' :
                        break;
                        case 'Diffusione':
                            fwrite($stream,'**'.$attribute->getData('name').'**'.PHP_EOL);
                             switch($attribute->getRawData('value')) {
                                 case 'Specie endemica':
                                     fwrite($stream,':   ●'.PHP_EOL);
                                 break;
                             }
                        break;
                        case 'Ciclo riproduttivo' :
                             fwrite($stream,'**'.$attribute->getData('name').'**'.PHP_EOL);
                             switch($attribute->getRawData('value')) {
                                 case 'Annuale':
                                     fwrite($stream,':   ☉'.PHP_EOL);
                                 break;
                                 case 'Biennale':
                                     fwrite($stream,':   ⚇'.PHP_EOL);
                                 break;
                                 default :
                                     fwrite($stream,':   '.$attribute->getRawData('value').PHP_EOL);
                                 break;
                             }
                        break;
                        case 'Portamento' :
                             fwrite($stream,'**'.$attribute->getData('name').'**'.PHP_EOL);
                             switch($attribute->getRawData('value')) {
                                 case 'Pianta perenne erbacea':
                                     fwrite($stream,':   ↓'.PHP_EOL);
                                 break;
                                 case 'Cespuglio':
                                     fwrite($stream,':   ⏉'.PHP_EOL);
                                 break;
                                 case 'Albero':
                                     fwrite($stream,':   ☨'.PHP_EOL);
                                 break;
                                 default :
                                     fwrite($stream,':   '.$attribute->getRawData('value').PHP_EOL);
                                 break;
                             }
                        break;
                        default: 
                            fwrite($stream,'**'.$attribute->getData('name').'**'.PHP_EOL);
                            fwrite($stream,':   '.$attribute->getRawData('value').PHP_EOL);
                        break;
                    }
                }
                $from = $attributeColl->filterByAttributeValue('Limite altitudinale inferiore','name')->getFirst()->getRawData('value');
                $to = $attributeColl->filterByAttributeValue('Limite altitudinale superiore','name')->getFirst()->getRawData('value');
                if (
                     $from != '' &&
                     $to != ''
                ) {
                    $altitudeImageUrl = $this->getAltitudeImage(
                            $from,
                            $to
                    );
                    fwrite($stream,'**Altitudine**'.PHP_EOL);
                    fwrite($stream,':   da '.$from.' a '.$to.' m'.PHP_EOL.PHP_EOL);
                    fwrite($stream,'![]('.$basePath.$altitudeImageUrl.')'.PHP_EOL);
                    
                }
            }
            fwrite($stream,PHP_EOL);
            $imageColl = $taxa->getTaxaImageColl();
            if ($imageColl->count() > 0) {
                foreach($imageColl->getItems() as $key=>$image) {
                    fwrite($stream,'![]('.$basePath.$image->getUrl().')'.PHP_EOL);
                }
                fwrite($stream,PHP_EOL);
            }
            
            $dicoItemColl = $taxa->getDicoItemColl();
            $childrensIds=array();
            $positions = array();
            $lastPosition = 0;
            if ($dicoItemColl->count() >0) {
                foreach ($dicoItemColl->getItems() as $dicoItem) {
                    if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getData('taxa_id') != 0 && $dicoItem->getRawData('status') == 1) {
                        array_push($childrensIds, $dicoItem->getData('taxa_id')); 
                    }
                    if ($dicoItem->getData('text') != '') {
                        $lastCharacter = substr($dicoItem->getData('id'),-1);
                        if ($lastCharacter == 0) {
                           $lastPosition++;
                           $positions[substr($dicoItem->getData('id'),0,-1).'0']= $lastPosition;
                           $positions[substr($dicoItem->getData('id'),0,-1).'1']= $lastPosition;
                        }
                        fwrite($stream,'| '.str_repeat('&#160;', (strlen($dicoItem->getData('id')))));
                        fwrite($stream,$positions[$dicoItem->getData('id')].' '.$dicoItem->getData('text'));
                        if ($dicoItem->getData('taxa_id')!= '' && $dicoItem->getRawData('status') == 0) {
                            fwrite($stream, ' '.$dicoItem->getRawData('initials').' '.$dicoItem->getRawData('name'));
                        } else if ($dicoItem->getData('taxa_id')!= '' && $dicoItem->getRawData('status') == 1) {
                            fwrite($stream, ' ['.$dicoItem->getRawData('initials').' '.$dicoItem->getRawData('name').'](#t'.$dicoItem->getData('taxa_id').')');
                        }
                        fwrite($stream,PHP_EOL);
                    }
                }
                fwrite($stream,PHP_EOL.'---------------'.PHP_EOL.PHP_EOL);
                $taxaToDo = array_merge($childrensIds,$taxaToDo);
            }
        }
        file_put_contents('php://stderr', 'Create Index'.str_repeat(' ', 20)."\r");
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
                case 'docx':
                case 'pdf':
                    $cmd = 'pandoc -f markdown+definition_lists+line_blocks --latex-engine=xelatex -s -o '.$tempFileName.'.'.$format.' '.$tempFileName.' 2>&1';
                case 'html':
                case 'html5':   
                    file_put_contents('php://stderr', 'Exporting'.str_repeat(' ', 20)."\r");
                    if (!isset($cmd)) {
                        $cmd = 'pandoc -t '.$format.' -f markdown+definition_lists+line_blocks --latex-engine=xelatex -s -o '.$tempFileName.'.'.$format.' '.$tempFileName.' 2>&1';
                    }
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
        file_put_contents('php://stderr', 'Done'.str_repeat(' ', 20)."\r");
    }
    /**
     * Gets the image url, if image is not present creates it
     * @param integer $from
     * @param integer $to
     * @return string image Url
     */
    private function getAltitudeImage($from,$to) {
        $from = preg_replace('/[^0-9]/','',$from);
        $to = preg_replace('/[^0-9]/','',$to);
        $nameFile = md5($from.'-'.$to);
        $basePath = $this->db->baseDir;
        $imageUrl = DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'merged_altitude';
        if (!is_dir($basePath.$imageUrl)) {
            mkdir($basePath.$imageUrl);
        }
        $imageUrl .= DIRECTORY_SEPARATOR.substr($nameFile, 0,1);
        if (!is_dir($basePath.$imageUrl)) {
            mkdir($basePath.$imageUrl);
        }
        $imageUrl .= DIRECTORY_SEPARATOR.substr($nameFile, 1,1);
        if (!is_dir($basePath.$imageUrl)) {
            mkdir($basePath.$imageUrl);
        }
        $imageUrl .= DIRECTORY_SEPARATOR.$nameFile.'.png';
        if (!is_file($basePath.$imageUrl)) {
            $images =array($this->db->baseDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'altitude'.DIRECTORY_SEPARATOR.'altitudine.png');
            for($c = intval(1+($from/$this->db->config->attributes->altitudeStep)); $c <= intval(1+($to/$this->db->config->attributes->altitudeStep));$c++) {
                array_push(
                    $images,
                    $this->db->baseDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'altitude'.DIRECTORY_SEPARATOR.$c*$this->db->config->attributes->altitudeStep.'.png'
                );
            }
            $this->mergeImages($images,$basePath.$imageUrl);
        }        
        return $imageUrl;
    }
    /**
     * Merges images array of path in one
     * @param array $images array of file names
     * @param string $destPath destination file
     */
    private function mergeImages($images,$destPath) {
        foreach ($images as $level=>$imagePath) {
            switch(strtolower(pathinfo($imagePath,PATHINFO_EXTENSION))) {
                case 'png' :
                    if ($level == 0) {
                        if(!$destImageResource = imagecreatefrompng($imagePath)) {
                            throw new \Exception('Unable to open the image '.$imagePath,1509231631);
                        }
                    } else {
                        if(!$imageResource = imagecreatefrompng($imagePath)) {
                            throw new \Exception('Unable to open the image '.$imagePath,1509231631);
                        }
                        if (!imagecopy($destImageResource,$imageResource,0,0,0,0,imagesx($imageResource),imagesy($imageResource))) {
                            throw new \Exception('Unable to copy the image '.$imagePath,1509231631);
                        }
                    }
                break;
                default :
                    throw new \Exception('File type '.strtolower(pathinfo($imagePath,PATHINFO_EXTENSION)).' not supported',1509231628);
                break;
            }
        }
        switch(strtolower(pathinfo($destPath,PATHINFO_EXTENSION))) {
                case 'png' :
                    if (!imagepng($destImageResource,$destPath)) {
                        throw new \Exception('Unable to write the file '.$destPath,1509231630);
                    }
                break;
                default :
                    throw new \Exception('File type '.strtolower(pathinfo($imagePath,PATHINFO_EXTENSION)).' not supported',1509231628);
                break;
        }
    }
}