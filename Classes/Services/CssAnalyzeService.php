<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 13.10.14
 * Time: 17:29
 */

namespace KayStrobach\Developer\Services;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class CssAnalyzeService
{
    /**
     * @var \TYPO3\CMS\Core\Html\HtmlParser
     */
    protected $htmlParser;

    /**
     *
     */
    public function __construct() 
    {
        $this->htmlParser = GeneralUtility::makeInstance('TYPO3\CMS\Core\Html\HtmlParser');
    }
    /**
     * Creates hierarchy of CSS selectors from input HTML content:
     *
     * @param  string        HTML body content
     * @param  integer        Max recursions
     * @param  string        Current selector prefix
     * @return array        Array with information about the found selectors.
     */
    function getHierarchy($HTMLcontent, $count = 20, $selPrefix = '') 
    {
        $parts = $this->htmlParser->splitIntoBlock(
            'a,b,blockquote,body,div,em,font,form,h1,h2,h3,h4,h5,h6,i,li,ol,option,p,pre,select,span,strong,table,td,textarea,tr,u,ul,iframe',
            $HTMLcontent,
            1
        );

        $thisSelectors = array();
        $exampleContentAccum = array();

        reset($parts);
        while(list($k,$v) = each($parts)) {
            if ($k%2 && $count) {
                $firstTag = $this->htmlParser->getFirstTag($v);
                $firstTagName = $this->htmlParser->getFirstTagName($v);
                $attribs = $this->htmlParser->get_tag_attributes_classic($firstTag, 1);
                $thisSel = $selPrefix.' '.$firstTagName;
                if ($attribs['class']) {
                    $this->foundSelectors[] = trim($thisSel.'.'.$attribs['class']);
                }
                if ($attribs['id']) {
                    $this->foundSelectors[] = trim($thisSel.'#'.$attribs['id']);
                }
                if ($attribs['class']) {
                    $thisSel.= '.'.$attribs['class'];
                } elseif ($attribs['id']) {
                    $thisSel.= '#'.$attribs['id'];
                } else {
                    $this->foundSelectors[] = trim($thisSel);
                }

                $v = $this->htmlParser->removeFirstAndLastTag($v);
                $pC = $this->getHierarchy($v, $count-1, $thisSel);
                $hash = md5(serialize($pC[1]));
                if (!isset($exampleContentAccum[$hash])) {        $exampleContentAccum[$hash]=$v; 
                }

                $parts[$k]=array(
                 'tag' => $firstTag,
                 'tagName' => $firstTagName,
                 'thisSel' => $thisSel,
                 'subContent' => $pC[0],
                 'accum_selectors' => $pC[1],
                 'accum_selectors_hash' => $hash,
                 'example_content' => $pC[2]
                );

                $thisSelectors = array_merge($thisSelectors, $pC['selectors']);
                $thisSelectors[] = $thisSel;
            } else {
                $parts[$k] = array();

                $singleParts = $this->htmlParser->splitTags('img,input,hr', $v);
                reset($singleParts);
                while(list($kk,$vv)=each($singleParts))    {
                    if ($kk%2) {
                        $firstTag = $this->htmlParser->getFirstTag($vv);
                        $firstTagName = $this->htmlParser->getFirstTagName($vv);
                        $attribs = $this->htmlParser->get_tag_attributes_classic($firstTag, 1);

                        $thisSel = $selPrefix . ' ' . $firstTagName;
                        if ($attribs['class']) {
                            $this->foundSelectors[] = trim($thisSel . '.' . $attribs['class']);
                        }
                        if ($attribs['id']) {
                            $this->foundSelectors[] = trim($thisSel . '#' . $attribs['id']);
                        }
                        if (!$attribs['class'] && !$attribs['id']) {
                            $this->foundSelectors[] = trim($thisSel);
                        }
                        $parts[$k][$kk] = array(
                         'tag' => $firstTag,
                         'tagName' => $firstTagName,
                         'thisSel' => $thisSel,
                        );

                        $thisSelectors[]=$thisSel;
                    }
                    $parts[$k][$kk]['content']=$vv;
                }
            }
        }

        asort($thisSelectors);
        $thisSelectors = array_unique($thisSelectors);

        return array(
         'parts' => $parts,
         'selectors' => $thisSelectors,
         'content' => implode('', $exampleContentAccum)
        );
    }
} 