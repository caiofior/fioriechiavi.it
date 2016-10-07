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
     * LATEX constant
     */
    const LATEX='latex';
    /**
     * CONTEXT constant
     */
    const CONTEXT='context';
    /**
     * PLAIN constant
     */
    const PLAIN='plain';
    /**
     * RTF constant
     */
    const RTF='rtf';
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
            fwrite($stream, '#1 '.$this->db->config->siteName.' - Chiave d\'insieme'.PHP_EOL.PHP_EOL);
        } else {
            $taxa->loadFromId($this->rootId);
            fwrite($stream, '#1 '.$this->db->config->siteName.' - Chiave '.$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').PHP_EOL.PHP_EOL);
            
            
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
                file_put_contents('php://stderr', $count."\t". (memory_get_peak_usage()/1024/1024)." Mb\t".$taxa->getData('name').str_repeat(' ', 20)."\r");
            }
            $taxaParentColl =  $taxa->getParentColl()->getItems();
            if ($taxa->getData('id') != 1) { 
                fwrite($stream, str_repeat('#', count($taxaParentColl)+1).' ');
                fwrite($stream, $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').' {#t'.$taxa->getData('id').'}'.PHP_EOL.PHP_EOL);
            }
            if ($taxa->getData('id') != $this->rootId) {
                end($taxaParentColl);
                $parentTaxa = current($taxaParentColl);
                if (is_object($parentTaxa)) {
                    fwrite($stream,'['.$parentTaxa->getRawData('taxa_kind_initials').' '.$parentTaxa->getData('name').'](#t'.$parentTaxa->getData('id').')'.PHP_EOL.PHP_EOL);
                }
                if($this->db->config->externalUrl != '') {
                    fwrite($stream,'[Scheda aggiornata - Segnala osservazione]('.$this->db->config->externalUrl.'?id='.$taxa->getData('id').')'.PHP_EOL.PHP_EOL);
                    if ($format == self::PDF) {
                        $qrUrl = $this->getQrOnlineUrl($taxa->getData('id'));
                        fwrite($stream,'![]('.$this->db->baseDir.$qrUrl.')'.PHP_EOL.PHP_EOL);
                    }
                }
            }
            if ($taxa->getData('description') != '') {
                $description = $taxa->getData('description');
                preg_match_all('/{t([[:alnum:]\/]+)}/',$description,$items);
                if (is_array($items) && array_key_exists(1, $items)) {
                    $relTaxa = new \flora\taxa\Taxa($GLOBALS['db']);
                    foreach ($items[1] as $progessNumber) {
                        $relTaxa->loadFromAttributeValue($GLOBALS['db']->config->attributes->progress,$progessNumber);
                        if ($relTaxa->getData('id') != '') {
                            $description = str_replace('{t'.$progessNumber.'}', '['.$relTaxa->getData('taxa_kind_initials').' '.$relTaxa->getData('name').'](#t'.$relTaxa->getData('id').')', $description);
                        }
                    }
                }
                fwrite($stream, $description.PHP_EOL.PHP_EOL);
            }
            
            $regionColl = $taxa->getRegionColl();
            $regionColl = $regionColl->filterByAttributeValue('1','selected');
            if ($regionColl->count() >0) {
                $regionImageUrl = $this->getRegionImage($regionColl->getFieldsAsArray('id'));
                fwrite($stream,'![Diffusione]('.$basePath.$regionImageUrl.')'.PHP_EOL.PHP_EOL);
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
                                     fwrite($stream,':   ● Specie endemica'.PHP_EOL);
                                 break;
                             }
                        break;
                        case 'Ciclo riproduttivo' :
                             fwrite($stream,'**'.$attribute->getData('name').'**'.PHP_EOL);
                             switch($attribute->getRawData('value')) {
                                 case 'Annuale':
                                     fwrite($stream,':   ☉ Annuale'.PHP_EOL);
                                 break;
                                 case 'Biennale':
                                     fwrite($stream,':   ⚇ Biennale'.PHP_EOL);
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
                                     fwrite($stream,':   ↓ Pianta perenne erbacea'.PHP_EOL);
                                 break;
                                 case 'Cespuglio':
                                     fwrite($stream,':   ⏉ Cespuglio'.PHP_EOL);
                                 break;
                                 case 'Albero':
                                     fwrite($stream,':   ☨ Albero'.PHP_EOL);
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
                $from = '';
                $to = '';
                $fromName = $attributeColl->filterByAttributeValue('Inizio fioritura','name')->getFirst()->getRawData('value');
                $toName = $attributeColl->filterByAttributeValue('Fine fioritura','name')->getFirst()->getRawData('value');
                $nameToNumber=$this->db->config->attributes->floweringNames->toArray();
                if (array_key_exists($fromName,$nameToNumber)) {
                    $from = $nameToNumber[$fromName];
                }
                if (array_key_exists($toName,$nameToNumber)) {
                    $to = $nameToNumber[$toName];
                }
                if (
                     $from != '' &&
                     $to != ''
                ) {
                    $floweringImageUrl = $this->getFloweringImage(
                            $from,
                            $to
                    );
                    fwrite($stream,'**Fioritura**'.PHP_EOL);
                    fwrite($stream,':   da '.$fromName.' a '.$toName.PHP_EOL.PHP_EOL);
                    fwrite($stream,'![]('.$basePath.$floweringImageUrl.')'.PHP_EOL);
                    
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
                        if (array_key_exists($dicoItem->getData('id'),$positions)) {
                           fwrite($stream,$positions[$dicoItem->getData('id')].' '.$dicoItem->getData('text'));
                        } else {
                           fwrite($stream,$dicoItem->getData('text'));
                        }
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
        file_put_contents('php://stderr', "\n".'Create Index'.str_repeat(' ', 20)."\r");
        if(count($taxaDone)>0) {
            asort($taxaDone);
            foreach($taxaDone as $taxaId => $taxaName) {
                $taxa->loadFromId($taxaId);
                fwrite($stream,'* ['.$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').'](#t'.$taxa->getData('id').')'.PHP_EOL);
            }
        }
        switch ($format) {
                case 'plain':    
                case 'rtf':
                case 'latex':
                case 'context':    
                case 'html':
                case 'html5':
                case 'odt':
                case 'doc':
                case 'docx':
                case 'pdf':
                    $cmd = 'pandoc --toc-depth=5 -f markdown+definition_lists+line_blocks --latex-engine=xelatex -s -o '.$tempFileName.'.'.$format.' '.$tempFileName.' 2>&1';
                case 'epub':
                    file_put_contents('php://stderr', 'Exporting'.str_repeat(' ', 20)."\r");
                    if (!isset($cmd)) {
                        $cmd = 'pandoc --toc-depth=5 -t '.$format.' -f markdown+definition_lists+line_blocks -s -o '.$tempFileName.'.'.$format.' '.$tempFileName.' 2>&1';
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
     * Gets altitude image url, if image is not present creates it
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
     * Gets flowering image url, if image is not present creates it
     * @param integer $from
     * @param integer $to
     * @return string image Url
     */
    private function getFloweringImage($from,$to) {
        $from = preg_replace('/[^0-9]/','',$from);
        $to = preg_replace('/[^0-9]/','',$to);
        $nameFile = md5($from.'-'.$to);
        $basePath = $this->db->baseDir;
        $imageUrl = DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'merged_flower';
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
            $images =array($this->db->baseDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'flower'.DIRECTORY_SEPARATOR.'months.png');
            for($c = $from ; $c <= $to ;$c++) {
                array_push(
                    $images,
                    $this->db->baseDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'flower'.DIRECTORY_SEPARATOR.str_pad($c,2,'0',STR_PAD_LEFT).'.png'
                );
            }
            $this->mergeImages($images,$basePath.$imageUrl);
        }        
        return $imageUrl;
    }
    /**
     * Gets region image url, if image is not present creates it
     * @param array $regionArray
     * @return string image Url
     */
    private function getRegionImage(array $regionArray) {
        sort($regionArray);
        $nameFile = md5(implode('-',$regionArray));
        $basePath = $this->db->baseDir;
        $imageUrl = DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'merged_map';
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
            $images =array($this->db->baseDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'map'.DIRECTORY_SEPARATOR.'italia_white.png');
            foreach ($regionArray as $c) {
                array_push(
                    $images,
                    $this->db->baseDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'map'.DIRECTORY_SEPARATOR.str_pad($c,2,'0',STR_PAD_LEFT).'.png'
                );
            }
            $this->mergeImages($images,$basePath.$imageUrl);
        }        
        return $imageUrl;
    }
    
    private function getQrOnlineUrl($taxaId) {
        $nameFile = str_pad($taxaId,3,'0',STR_PAD_LEFT);
        $basePath = $this->db->baseDir;
        $imageUrl = DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'qr';
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
            \QRcode::png($this->db->config->externalUrl.'?id='.$taxaId, $basePath.$imageUrl);
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
                        imagedestroy($imageResource);
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
                    imagedestroy($destImageResource);
                break;
                default :
                    throw new \Exception('File type '.strtolower(pathinfo($imagePath,PATHINFO_EXTENSION)).' not supported',1509231628);
                break;
        }
    }
}