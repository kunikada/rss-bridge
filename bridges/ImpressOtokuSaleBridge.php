<?php
class ImpressOtokuSaleBridge extends BridgeAbstract
{
    const NAME = 'ImpressOtokuSale';
    const URI = 'https://www.watch.impress.co.jp/otoku-sale/';
    const DESCRIPTION = 'Advantageous sale information summary';
    const MAINTAINER = 'kunikada';
    const PARAMETERS = [];
    const CACHE_TIMEOUT = 3600;

    const URI_PREFIX = 'https://www.watch.impress.co.jp';

    private function toTimestamp($href, $date)
    {
        if ($ret = $this->loadCacheValue($href)) {
            return $ret;
        }

        $html = getSimpleHTMLDOM($href);
        $ret = $html->find('meta[property=date]', 0)->content;
        if (!$ret) {
            $ret = $html->find('meta[name=creation_date]', 0)->content;
        }

        if (!$ret) {
        $date = date_replace('（', '', $date);
        $date = date_replace('年', '-', $date);
        $date = date_replace('月', '-', $date);
        $date = date_replace('日）', '', $date);
        $ret = $date;
        }
        $this->saveCacheValue($href, $ret);
        return $ret;
    }

    private function toImage($str)
    {
        if ($str === null) {
            return null;
        }
        return self::URI_PREFIX . $str;
    }

    public function collectData()
    {
        $html = getSimpleHTMLDOM($this->getUri());
        foreach ($html->find('li.otoku-sale') as $item) {
            $title = $item->find('p.title a', 0);
            $this->items[] = [
                'uri' => $title->href,
                'title' => $title->innertext,
                'timestamp' => $this->toTimestamp($title->href, $item->find('p.date', 0)->innertext),
                'author' => parse_url($title->href, PHP_URL_HOST),
                //'content' => $item->find('p.outline', 0)->innertext,
                //'enclosures' => [$this->toImage($item->find('div.image img', 0)->ajax)],
                ];
        }
    }
}

