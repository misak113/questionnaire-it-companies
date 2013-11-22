<?php
/**
 * Created by PhpStorm.
 * User: misak113
 * Date: 21.11.13
 * Time: 19:15
 */

namespace app\model;


use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;
use Nette\Utils\Strings;

class AresModel {
    /** @var string  */
    protected $url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?obchodni_firma=%s&diakritika=ASCII&max_pocet=100';
    /** @var \Nette\Caching\Cache @inject */
    protected $cache;

    public function setCache($tempDir) {
        $cacheDir = $tempDir.'/cache/ARES';
        if (!is_dir($cacheDir))
            @mkdir($cacheDir, 0777, true);
        $this->cache = new Cache(new FileStorage($cacheDir));
    }

    /**
     * @param string $name
     * @return array
     */
    public function getCompaniesByName($name) {
        $name = Strings::webalize($name, 'A-Za-z0-9 ');
        $companies = $this->getFromCache($name);
        if (count($companies) > 20)
            return array_slice($companies, 0, 20, true);

        if (strlen($name) <= 3)
            return $companies;

        $companies = $companies + $this->getFromAres($name);
        $this->addToCache($companies);

        $companies = array_slice($companies, 0, 20, true);

        return $companies;
    }

    protected function getFromCache($name) {
        $companiesCached = $this->cache->load('companies');
        if (!$companiesCached)
            return array();

        $companies = array();
        foreach ($companiesCached as $company) {
            if (preg_match('~^'.$name.'~', $company['name']))
                $companies[$this->hash($company)] = $company;
        }

        return $companies;
    }

    protected function addToCache(array $companies) {
        $companiesCached = $this->cache->load('companies');
        if (!$companiesCached)
            $companiesCached = array();
        $companies = $companies + $companiesCached;
        $this->cache->save('companies', $companies);
    }

    protected function getFromAres($name) {
        if ($this->isFail($name)) {
            Debugger::log('Příliš obecný dotaz do ARESu. z cache', Debugger::WARNING);
            return array();
        }

        // stáhne z ARESu
        $url = sprintf($this->url, $name.'*');
        $response = @file_get_contents($url);

        // pokud zfailuje, tak se už neptá
        if (strpos($response, '<dtt:Error_kod>1</dtt:Error_kod>') !== false) {
            Debugger::log('Příliš obecný dotaz do ARESu. online', Debugger::ERROR);
            $this->cacheFail($name);
            return array();
        }

        $xml = \XmlHelper::xml2array($response);
        $records = isset($xml['are:Ares_odpovedi']['are:Odpoved']['are:Zaznam'])
            ?$xml['are:Ares_odpovedi']['are:Odpoved']['are:Zaznam']
            :null;

        if (!$records) {
            Debugger::log('Nebyly nalezeny záznamyve správné struktuře v XML', Debugger::ERROR);
            return array();
        }Debugger::log($records);

        $companies = array();
        foreach ($records as $record) {
            $address = isset($record['are:Identifikace'])
                    && is_array($record['are:Identifikace'])
                    && isset($record['are:Identifikace']['are:Adresa_ARES']['dtt:Nazev_okresu'])
                ?$record['are:Identifikace']['are:Adresa_ARES']
                :array('dtt:Nazev_okresu' => '', 'dtt:Nazev_obce' => '', 'dtt:Nazev_casti_obce' => ''
                    , 'dtt:Nazev_ulice' => '', 'dtt:Cislo_domovni' => '', 'dtt:Cislo_orientacni' => ''
                    , 'dtt:PSC' => '');

            $company = array(
                'ic' => isset($record['are:ICO']) ?$record['are:ICO'] :'',
                'name' => isset($record['are:Obchodni_firma']) ?$record['are:Obchodni_firma'] :'',
                'size' => '',
                'address_city' => $address['dtt:Nazev_okresu'].' - '.$address['dtt:Nazev_obce'].' - '.$address['dtt:Nazev_casti_obce'],
                'address_street' => $address['dtt:Nazev_ulice'].' '.$address['dtt:Cislo_domovni'].'/'.$address['dtt:Cislo_orientacni'],
                'address_postcode' => $address['dtt:PSC'],
            );
            $companies[$this->hash($company)] = $company;
        }

        return $companies;
    }

    protected function cacheFail($name) {
        $this->cache->save(array($name, 'fail'), true);
    }

    protected function isFail($name) {
        return (bool)$this->cache->load(array($name, 'fail'));
    }

    protected function hash(array $x) {
        return sha1(Json::encode($x));
    }

}