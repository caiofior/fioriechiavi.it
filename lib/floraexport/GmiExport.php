<?php
namespace floraexport;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Exports data in gmi format
 *
 * @author caiofior
 */
class GmiExport {
    /**
     * Base directory
     * @var string
     */
    private $baseDir =null;
    /**
     * Initial id
     * @var string
     */
    private $id =0;
    /**
     * Resource where write log
     * @var resource
     */
    private $log = null;
    /**
     * Parsed ids
     * @var array
     */
    private $ids=array();
    /**
     * Sets initial data
     * @param type $baseDir
     * @param type $id
     * @param type $log
     */
    public function __construct($baseDir, $id,$log=null) {
        $this->baseDir=$baseDir;
        $this->id=$id;
        $this->log=$log;
    }
    /**
     * Parses data
     */
    public function parse () {
        $this->recursiveParse($this->id);
    }
    /**
     * Recursive data parse
     * @param int $id
     */
    private function recursiveParse($id) {
        $gmiText = '';
        if (in_array($id, $this->ids)) {
            return;
        }
        $this->ids[]=$id;
        $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
        $taxa->loadFromId($id);

        if ($taxa->getData('id')> 1) {
            $gmiText .= '# ' . $taxa->getRawData('taxa_kind_initials') . ' ' . $taxa->getData('name') . PHP_EOL;
            $gmiText .= PHP_EOL;
        }
        $gmiText .= strip_tags($taxa->getData('description')) . PHP_EOL;
        $gmiText .= PHP_EOL;
        
        $regionColl = $taxa->getRegionColl();
        $regionColl = $regionColl->filterByAttributeValue('1', 'selected');
        if ($regionColl->count() > 0) {
            $gmiText .= '## Diffusione' . PHP_EOL;
            foreach ($regionColl->getItems() as $region) {
                $gmiText .= $region->getData('name') . PHP_EOL;
            }
        }

        $attributeColl = $taxa->getTaxaAttributeColl();
        if ($attributeColl->count() > 0) {
            foreach ($attributeColl->getItems() as $attribute) {
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
                        $gmiText .= '## ' . $attribute->getData('name') . '**' . PHP_EOL;
                        switch ($attribute->getRawData('value')) {
                            case 'Specie endemica':
                                $gmiText .= '● Specie endemica' . PHP_EOL;
                                break;
                        }
                        break;
                    case 'Ciclo riproduttivo' :
                        $gmiText .= '## ' . $attribute->getData('name') . PHP_EOL;
                        switch ($attribute->getRawData('value')) {
                            case 'Annuale':
                                $gmiText .= '☉ Annuale' . PHP_EOL;
                                break;
                            case 'Biennale':
                                $gmiText .= '⚇ Biennale' . PHP_EOL;
                                break;
                            default :
                                $gmiText .= $attribute->getRawData('value') . PHP_EOL;
                                break;
                        }
                        break;
                    case 'Portamento' :
                        $gmiText .= '## ' . $attribute->getData('name') . PHP_EOL;
                        switch ($attribute->getRawData('value')) {
                            case 'Pianta perenne erbacea':
                                $gmiText .= '↓ Pianta perenne erbacea' . PHP_EOL;
                                break;
                            case 'Cespuglio':
                                $gmiText .= '⏉ Cespuglio' . PHP_EOL;
                                break;
                            case 'Albero':
                                $gmiText .= '☨ Albero' . PHP_EOL;
                                break;
                            default :
                                $gmiText .= $attribute->getRawData('value') . PHP_EOL;
                                break;
                        }
                        break;
                    default:
                        $gmiText .= '## ' . $attribute->getData('name') . PHP_EOL;
                        $gmiText .= $attribute->getRawData('value') . PHP_EOL;
                        break;
                }
            }
            $from = $attributeColl->filterByAttributeValue('Limite altitudinale inferiore', 'name')->getFirst()->getRawData('value');
            $to = $attributeColl->filterByAttributeValue('Limite altitudinale superiore', 'name')->getFirst()->getRawData('value');
            if (
                    $from != '' &&
                    $to != ''
            ) {
                $gmiText .= '## Altitudine' . PHP_EOL;
                $gmiText .= 'da ' . $from . ' a ' . $to . ' m' . PHP_EOL;
            }
            $from = '';
            $to = '';
            $fromName = $attributeColl->filterByAttributeValue('Inizio fioritura', 'name')->getFirst()->getRawData('value');
            $toName = $attributeColl->filterByAttributeValue('Fine fioritura', 'name')->getFirst()->getRawData('value');
            $nameToNumber = $GLOBALS['db']->config->attributes->floweringNames->toArray();
            if (array_key_exists($fromName, $nameToNumber)) {
                $from = $nameToNumber[$fromName];
            }
            if (array_key_exists($toName, $nameToNumber)) {
                $to = $nameToNumber[$toName];
            }
            if (
                    $from != '' &&
                    $to != ''
            ) {
                $gmiText .= '## Fioritura' . PHP_EOL;
                $gmiText .= 'da ' . $fromName . ' a ' . $toName . PHP_EOL;
            }
        }

        $imageColl = $taxa->getTaxaImageColl();
        if ($imageColl->count() > 0) {
            foreach ($imageColl->getItems() as $key => $image) {
                $this->copyImagePath($image->getData('id'));
                $url = $image->getUrl();
                if(!is_null($url)) {
                    if ($id == 1) {
                        $gmiText .= '=> ' . $url . PHP_EOL;
                    } else {
                        $gmiText .= '=> ../../' . $url . PHP_EOL;
                    }
                }
            }
        }
        $gmiText .= PHP_EOL;
        $gmiText .= '=> ' . $GLOBALS['db']->config->externalUrl . '/index.php?id=' . $taxa->getData('id') . ' Scheda aggiornata ' . PHP_EOL;
        $gmiText .= PHP_EOL;
        $dicoItemColl = $taxa->getDicoItemColl();
        $childrensIds = array();
        $positions = array();
        $lastPosition = 0;
        if ($dicoItemColl->count() > 0) {
            foreach ($dicoItemColl->getItems() as $dicoItem) {
                if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getData('taxa_id') != 0 && $dicoItem->getRawData('status') == 1) {
                    array_push($childrensIds, $dicoItem->getData('taxa_id'));
                }
                if ($dicoItem->getData('text') != '') {
                    $lastCharacter = substr($dicoItem->getData('id'), -1);
                    if ($lastCharacter == 0) {
                        $lastPosition++;
                        $positions[substr($dicoItem->getData('id'), 0, -1) . '0'] = $lastPosition;
                        $positions[substr($dicoItem->getData('id'), 0, -1) . '1'] = $lastPosition;
                    }
                    $gmiText .= '| ' . str_repeat(' ', (strlen($dicoItem->getData('id'))));
                    if (array_key_exists($dicoItem->getData('id'), $positions)) {
                        $gmiText .= $positions[$dicoItem->getData('id')] . ' ' . $dicoItem->getData('text');
                    } else {
                        $gmiText .= $dicoItem->getData('text');
                    }
                    if ($dicoItem->getData('taxa_id') != '' && $dicoItem->getRawData('status') == 0) {
                        $gmiText .= ' ' . $dicoItem->getRawData('initials') . ' ' . $dicoItem->getRawData('name');
                    } else if ($dicoItem->getData('taxa_id') != '' && $dicoItem->getRawData('status') == 1) {
                        $gmiText .= PHP_EOL . '=> ' . $this->getFileUrl($dicoItem->getData('taxa_id'), $taxa->getData('id')) . ' ' . $dicoItem->getRawData('initials') . ' ' . $dicoItem->getRawData('name');
                    }
                    $gmiText .= PHP_EOL;
                }
            }
        }
        if (
                is_resource($this->log) &&
                sizeof($this->ids) % 100 == 0
                ) {
            fwrite($this->log, sizeof($this->ids). "\t".$taxa->getData('name'). str_repeat(' ', 100) ."\r");
        }
        file_put_contents($this->getFilePath($taxa->getData('id')), $gmiText);
        foreach ($childrensIds as $id) {
            $this->recursiveParse($id);
        }
    }
    /**
     * Gets file url
     * @param int $id
     * @param int $curId
     * @return string
     */
    private function getFileUrl($id, $curId) {
        $url = '';
        if ($curId > 1) {
            $url .= '../../';
        }
        $url .= str_pad(intval($id / 1e4), 2, '0');
        $url .= '/';
        $url .= str_pad(intval($id / 100), 2, '0');
        $url .= '/' . str_pad(substr($id, -2), 2, '0', STR_PAD_LEFT) . '.gmi';

        return $url;
    }
    /**
     * Copy image
     * @param int $id
     * @return string
     */
    private function copyImagePath($id) {
        $url = '';
        $orPath = $this->baseDir . '/../images/taxa/';
        $path = $this->baseDir . '/images/taxa/';
        if (!is_dir($path)) {
            mkdir($this->baseDir . '/images');
            mkdir($this->baseDir . '/images/taxa');
        }

        $url .= str_pad(intval($id / 1e4), 2, '0');
        if (!file_exists($path . $url)) {
            mkdir($path . $url);
        }
        $url .= '/';
        $url .= str_pad(intval($id / 100), 2, '0');
        if (!file_exists($path . $url)) {
            mkdir($path . $url);
        }
        $url .= '/' . substr($id, -2) . '.png';
        if (is_file($orPath . $url)) {
            copy($orPath . $url, $path . $url);
        } else {
            $url = null;
        }
        return $url;
    }
    /**
     * Creates gmi absolute path
     * @param int $id
     * @return string
     */
    private function getFilePath($id) {
        $path = $this->baseDir . '/';
        if ($id == 1) {
            $path .= 'index.gmi';
        } else {
            $path .= str_pad(intval($id / 1e4), 2, '0');
            if (!file_exists($path)) {
                mkdir($path);
            }
            $path .= '/';
            $path .= str_pad(intval($id / 100), 2, '0');
            if (!file_exists($path)) {
                mkdir($path);
            }
            $path .= '/' . str_pad(substr($id, -2), 2, '0', STR_PAD_LEFT) . '.gmi';
        }
        return $path;
    }

}
