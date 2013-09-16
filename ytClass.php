<?php
class ytubeCore {

    public function getList ($user){
        $response=file_get_contents('http://gdata.youtube.com/feeds/api/videos?author='.$user.'&max-results=50&v=2&alt=json');
        $jsonArray=json_decode($response, true);
        $array=array();

       foreach ($jsonArray['feed']['entry'] as $v){
            $array[]=ytubeCore::getVideoInfo($v);
        }
        return $array;
    }

    public function getFieldList ($user){
        $array=ytubeCore::getList($user);
        $list=array(''=>t('none'));
        foreach ($array as $a){
            $list[$a->getValue('id')]=$a->getValue('title');
        }
        return $list;
    }

    public function getVideo($id){
        $json = file_get_contents("http://gdata.youtube.com/feeds/api/videos/".$id."?v=2&alt=json");
        $arrayJson = json_decode($json, true);
        $video=ytubeCore::getVideoInfo($arrayJson['entry']);
        return $video;
    }

    public function getVideoInfo ($json){
        $video=new ytVideo;
        debug($json);
        $video->setValues(array(
            'id'=>$json['media$group']['yt$videoid']['$t'],
            'title'=>$json['title']['$t'],
            'author'=>$json['author'][0]['name']['$t'],
            'desc'=>$json['media$group']['media$description']['$t'],
            'thumbnail'=>'<img class="immagine-piccola" src="'.$json['media$group']['media$thumbnail'][3]['url'].'" />',
            'visual'=>$json['yt$statistics']['viewCount']
        ));
        return $video;
    }

    public function getId ($url){
        if(filter_var($url, FILTER_VALIDATE_URL)){
            parse_str(parse_url( $url, PHP_URL_QUERY ), $array);
            return $array['v'];
        }else{
            return $url;
        }
    }

    public function getContentsPlayer($id, $p=NULL){
       /* $defp=array(
            'yt_width'=>'560',
            'yt_height'=>'315',
            'yt_title'=>FALSE,
            'yt_desc'=>FALSE,
            'yt_thumbnail'=>FALSE
        );
        $p=array_merge($defp, $p);*/
        $video=ytubeCore::getVideo($id); 
        $content=array();
        $content['player'] = '
            <iframe
                width="'.$p['yt_width'].'" 
                height="'.$p['yt_height'].'" 
                src="http://www.youtube.com/embed/'.$id.'?rel=0"
                frameborder="0"
                allowfullscreen>
            </iframe>
        ';

        if($p['yt_title']==true)$content['title']=$video->getValue('title');
        if($p['yt_desc']==true)$content['desc']=$video->getValue('desc');

        return $content;
    }

    public function getContentsLink($id, $p=NULL){
        /*$defp=array(
            'yt_width'=>'560',
            'yt_height'=>'315',
            'yt_title'=>FALSE,
            'yt_desc'=>FALSE,
            'yt_thumbnail'=>FALSE
        );
        $p=array_merge($defp, $p);*/
        $video=ytubeCore::getVideo($id); 
        $content=array();
        $content['url'] = 'http://www.youtube.com/watch?v='.$id;
        $content['link'] = '<a href="'.$content['url'].'">youtube video -></a>';
        if($p['yt_title']==true)$content['title']=$video->getValue('title');
        if($p['yt_desc']==true)$content['desc']=$video->getValue('desc');
        if($p['yt_thumbnail']==true)$content['thumbnail']=$video->getValue('thumbnail');

        return $content;
    }

    public function getTemplate($tpl, $name){
        include(dirname(__FILE__).'/'.$name.'.tpl.inc');
        return $html;
    }

    public function replace($array, $code){
        foreach ($array as $k=>$v){
            $code=str_replace('['.$k.']', $v, $code);
        }
        return $code;
    }

    public function subText ($text, $limit){
        while($text[$limit]!=" ")$limit--;
        return substr($text,0,$limit).'...';
    }

}

class ytVideo {
    private $id;
    private $title;
    private $author;
    private $desc;
    private $thumbnail;
    private $visual;
    private $other;

    public function setValue ($k, $v){
        $this->$k=$v;
    }

    public function setValues ($array){
        foreach ($array as $k=>$v){
            $this->setValue($k, $v);
        }
    }

    public function getValue ($k){
        return $this->$k;
    }
}
?>