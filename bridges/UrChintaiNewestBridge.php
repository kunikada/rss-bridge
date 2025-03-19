<?php
class UrChintaiNewestBridge extends BridgeAbstract 
{
    const NAME = 'UR Chintai Newest';
    const URI = 'https://www.ur-net.go.jp';
    const DESCRIPTION = 'Feed UR Chintai newest items';
    const MAINTAINER = 'kunikada';
    const PARAMETERS = [
        [
            'area' => [
                'name' => 'Area',
                'type' => 'list',
                'required' => true,
                'values' => [
                    'tokyo' => 13,
                ],
                'defaultValue' => 13,
            ],
        ]
    ];
    const CACHE_TIMEOUT = 3600;
    const AUTHOR = 'UR賃貸住宅';
    const API_ENDPOINT = 'https://chintai.r6.ur-net.go.jp/chintai/api/bukken/result/bukken_result/';

    public function collectData()
    {
        $json = getContents(self::API_ENDPOINT, 
            ['Content-Type: application/x-www-form-urlencoded'],
            [CURLOPT_POST => 1]
        );
        $result = Json::decode($json);

        foreach ($result as $bukken) {
            $danchiNm = $bukken['danchiNm'];
            foreach ($bukken['room'] as $room) {
                $this->items[] = [
                    'uri' => $this->getUri().$room['roomLinkSp'],
                    'title' => sprintf('%s %s %s', $danchiNm, $room['roomNmMain'], $room['roomNmSub']),
                    //'timestamp' => 
                    'author' => self::AUTHOR,
                    'content' => sprintf('%s(%s) %s&frasl;%s %s', $room['rent'], $room['commonfee'], $room['type'], $room['floorspace'], $room['floor']),
                    'uid' => $room['id'],
                ];
            }
        }
    }
}