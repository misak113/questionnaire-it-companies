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

        $ok = preg_match_all('~<are:ICO>(\d+)</are:ICO>~', $response, $ics, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<are:Obchodni_firma>(.+)</are:Obchodni_firma>~', $response, $names, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:Nazev_ulice>(.+)</dtt:Nazev_ulice>~', $response, $streets, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:Cislo_domovni>(\d+)</dtt:Cislo_domovni>~', $response, $cps, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:Cislo_orientacni>(\d+)</dtt:Cislo_orientacni>~', $response, $cos, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:PSC>(\d+)</dtt:PSC>~', $response, $postcodes, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:Nazev_obce>(.+)</dtt:Nazev_obce>~', $response, $obces, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:Nazev_casti_obce>(.+)</dtt:Nazev_casti_obce>~', $response, $castiObces, PREG_SET_ORDER);
        $ok = $ok && preg_match_all('~<dtt:Nazev_mestske_casti>(.+)</dtt:Nazev_mestske_casti>~', $response, $castiMestas, PREG_SET_ORDER);
        if (!$ok) {
            Debugger::log('Nenašlo žádné části v XML', Debugger::WARNING);
            return array();
        }

        if (count($ics) !== count($names) || count($names) !== count($streets)
            || count($streets) !== count($cps) || count($cps) !== count($cos)
            || count($cos) !== count($postcodes) || count($postcodes) !== count($obces)
            || count($obces) !== count($castiObces) || count($castiObces) !== count($castiMestas)) {
            Debugger::log(Json::encode(array($ics, $names, $streets, $cps, $cos, $postcodes, $obces, $castiObces, $castiMestas)));
            Debugger::log('Některých položek není v XML stejně', Debugger::ERROR);
            return array();
        }

        $companies = array();
        foreach ($ics as $i => $notUsed) {
            $company = array(
                'ic' => $ics[$i][1],
                'name' => $names[$i][1],
                //'size' => $sizes[$i][1],
                'address_city' => $obces[$i][1].' - '.$castiObces[$i][1].' - '.$castiMestas[$i][1],
                'address_street' => $streets[$i][1].' '.$cps[$i][1].'/'.$cos[$i][1],
                'address_postcode' => $postcodes[$i][1],
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