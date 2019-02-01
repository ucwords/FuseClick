<?php
/**
 * Created by PhpStorm.
 * User: Dyson
 * Date: 2018/9/13
 * Time: 17:14
 */

namespace App\Models;


class GetCreativeStorage
{
    public static function dealCreative($platform, $package_name = '', $format = 'text')
    {

        if ($platform == 'android') {

            return self::getGoogle($package_name);
        }
        //$package_name = '297606951';

        $url = 'https://itunes.apple.com/us/lookup?id='.$package_name;
        $html_json_data = self::creativeGet($url);
        //dd($html_doc);
        //$html_json_data = json_encode($html_doc, true);
        //dd($html_json_data);
        $result = [];
        if (empty($html_json_data['results'])) {
            return false;
        }
        $result['icon'] = $html_json_data['results'][0]['artworkUrl512'];
        //$result['description'] = $html_json_data['results'][0]['description'];
        //$result['min_os_vs'] = $html_json_data['results'][0]['minimumOsVersion'];
        //$result['category'] = $html_json_data['results'][0]['primaryGenreName'];
        $result['screenshot'] = [];
        if(is_array($html_json_data['results'][0]["screenshotUrls"])){
            foreach($html_json_data['results'][0]["screenshotUrls"] as $src){
                $result['screenshot'][] = $src;
            }
        }
        if ($format == 'json') {
            return response()->json($result);
        }
        return $result;

    }

    public static function getGoogle($package_name='me.piebridge.brevent',$hl='en'){
        $url = 'https://play.google.com/store/apps/details?id=' . $package_name . '&hl='.$hl;
        $html = self::creativeGet($url);
        if (!$html) return false;

        try{
            $dom = new  \HtmlParser\ParserDom($html);
            $info = [];
           // $info['offer_name'] = $dom->find('h1[itemprop="name"]',0)->getPlainText();
           // $info['des'] = $dom->find('[itemprop="description"]',0)->getAttr('content');
            $info['icon'] = $dom->find('img[itemprop="image"]',0)->getAttr('src');
           // $tmp = $dom->find('a[itemprop="genre"]',0)->getAttr('href');
            //$tmp = explode('/',$tmp);
            //$info['category'] = end($tmp);
            $tmp = $dom->find('[data-screenshot-item-index] img');
            $info['screenshot'] = [];
            foreach($tmp as $d){
                $info['screenshot'][] = $d->getAttr('src');
            }
            return $info;
        }catch(\Exception $e){
            return json_encode(['error'=>'抓取过程中出错']);
        }

    }

    public static function creativeGet($url,$post_data=false,$ignore_ssl=true, $dataType='json')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'Chrome 42.0.2311.135 Pentamob');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);

        if($ignore_ssl){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //信任任何证书
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名,0不验证
        }

        if($post_data){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        }

        $data = curl_exec($curl);
        $status = curl_getinfo($curl);
        $error_info = [
            'error_no'   => curl_errno($curl),
            'error_info' => curl_getinfo($curl),
            'error_msg'  => curl_error($curl),
            'result'     => $data
        ];

        curl_close($curl);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            if ($dataType == 'json') {
                $data = json_decode($data, true);
            }
            return $data;
        } else {
            return false;
        }
    }
}