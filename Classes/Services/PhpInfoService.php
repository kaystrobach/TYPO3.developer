<?php


namespace KayStrobach\Developer\Services;



class PhpInfoService
{
    static public function extractPhpInfoData() 
    {
        ob_start();
        phpinfo();
        $phpinfo = array('phpinfo' => array());
        preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER);

        foreach($matches as $match) {
            if(strlen($match[1])) {
                $phpinfo[$match[1]] = array();
            } elseif(isset($match[3])) {
                $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : array($match[3]);
            } else {
                $phpinfo[end(array_keys($phpinfo))][] = $match[2];
            } 
        }
        return $phpinfo;
    }
}