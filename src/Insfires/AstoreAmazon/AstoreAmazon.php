<?php namespace Insfires\AstoreAmazon;

class AStore{
    protected $htmls = null;
    public function __construct($country = 'us', $tld = '.com' , $tid = 'bestratecellr-20'){
        $this->country = $country;
        $this->tld = $tld;
        $this->tid = $tid;
    }

    public function get($asin){
        $urlContents = 'http://astore.amazon'.$this->tld.'/'.$this->tid.'/detail/'.$asin.'/';

        $htmls = $this->zm_get_source($urlContents);
        $htmls = $this->str_get_html($htmls);
        $this->htmls = $htmls;
        $title = $this->gettitle();
        if($title['title'] && $title['title'] != null){
            $price = $this->getprice();
            $description = $this->getdescription();
            $detail = $this->getdetail();
            $editorial = $this->geteditorialreview();
            $review = $this->getreview();
            $image = $this->getimage();
            $rating = $this->getratings();
            $similar = $this->getsimilar();
            $images = $this->getallimages($asin);
            $hasil = array_merge($title,$price,$description,$detail,$editorial,$rating,$review,$image,$similar,$images);
            return $hasil;
        }else{
            return false;
        }
    }

    public function Cek($asin,$term=false){
        $urlContents = 'http://astore.amazon'.$this->tld.'/'.$this->tid.'/detail/'.$asin.'/';
        $htmls = $this->zm_get_source($urlContents);
        $htmls = $this->str_get_html($htmls);
        $this->htmls = $htmls;
        $cek = $this->gettitle();
        if($cek['title']!= null){
            if($term == true){
                $image = $this->getimage();
                return array_merge($cek,$image);
            }else{
                return $cek['title'];
            }
        }else{
            return false;
        }
    }
    public function getallimages($asin){
        $urlContents = 'http://astore.amazon'.$this->tld.'/'.$this->tid.'/images/'.$asin.'/';
        $htmls = $this->zm_get_source($urlContents);
        $images = array();
        $htmls = $this->str_get_html($htmls);
        if($htmls->find('li') != false){
            $list = $htmls->find('li');
            foreach($list as $li){
                $li = $li->find('img',0)->src;
                $images[] = str_replace(array('._SL30_.','jpg','png','http://ecx.images-amazon.com/images/I/'),'',$li);
            }
        }
        $hasil['images'] = $images;
        return $hasil;
    }
    public function gettitle(){
        $title = '';
        $by ='';
        $htmls = $this->htmls;
        if($htmls->find('div#titleAndByLine') != false){
            $htmlnya = $htmls->find('div#titleAndByLine',0)->innertext;
            if($data = $this->lz_cutter($htmlnya,'<h2>','</h2>')){

            }else{
                return false;
            }
            if($seller = $this->lz_cutter($htmlnya,'<span class="by">','</span>')){

            }else{
                $seller = '';
            }
            $title = str_replace($seller,'',$data);
            $title = trim(strip_tags($title));
            $arrayby = array("From ","Von ","De ");
            $by = str_replace($arrayby,'',$seller);
            $by = trim($by);
        }
        $result = array (
            "title" => $title,
            "vendor" => $by
        );

        return($result);
    }

    public function getimage(){
        $image = '';
        $htmls = $this->htmls;
        if($htmls->find('img#detailProductImage') != false){
            $htmlnya = $htmls->find('img#detailProductImage',0);
            $print = $htmlnya->src;
            $image = str_replace(array('._SL210_.','jpg','png','http://ecx.images-amazon.com/images/I/'),'',$print);
        }
        $return = array("image" => $image);
        return $return;
    }

    public function getprice(){
        $htmls = $this->htmls;
        $data = array();
        $listprice = '';
        $price = '';
        $deal = '';
        if($htmls->find('table#prices') != false){
            $htmlnya = $htmls->find('table#prices',0);
            $html = $this->str_get_html($htmlnya);
            foreach($html->find('tr') as $e) {
                $data[] = $e->innertext;

            }
            foreach ($data as $img){
                $tr = $this->str_get_html($img);

                foreach ($tr->find('span#detailListPrice') as $a){
                    $listprices = $a->innertext;
                    $listprice = $listprices;
                }


                foreach ($tr->find('span#detailOfferPrice') as $b){
                    $prices = $b->innertext;
                    $price = $prices;
                }

                foreach ($tr->find('span.supersaver') as $c){
                    $deals = $c->innertext;
                    $deal = str_replace('& ','',$deals);
                }
            }
        }
        $result = array(
            "price"=>$price,
            "price_list"=>$listprice,
            "deal"=>trim($deal)
        );
        return($result);
    }

    public function getratings(){
        $htmls = $this->htmls;
        if($htmls->find('table#detailheader',0) != false){
            $htmlnya = $htmls->find('table#detailheader',0);
            $html = $this->str_get_html($htmlnya);
            $a = $html->find('tr',0);
            $b = $a->innertext;
            if (strpos($b,'src="http://images.amazon.com/images/G/01/associates/network/') != false){
                preg_match('#src="http://images.amazon.com/images/G/01/associates/network/([^"]+)"></a>#', $b, $c);
                $d = $c[1];
                $star = $this->lz_cutter($d,'star','_tpng');
                $star = $star/10;
                $e = $c[0];
                $f = explode($e,$b);
                $z = $f[1];
                $g = $this->str_get_html($z);
                $h = $g->find('a',0);
                $i = $h->plaintext;
            }
            else {
                $star = '3.0';
                $i = 17;
            }
        }
        $hasil['rating']['star'] = $star;
        $hasil['rating']['total'] = $i;
        return $hasil;
    }

    public function getdescription(){
        $htmls = $this->htmls;
        $description = '';
        $shortdescription ='';
        if($htmls->find('div#productDescription') != false){
            $htmlnya = $htmls->find('div#productDescription',0);
            if($htmlnya->find('h2') != false){
                $htail = $htmlnya->find('h2',0);
                $htailnya = $htail->outertext;
                $return = str_replace($htailnya,'',$htmlnya);
                $return = str_replace('<p>','',$return);
                $return = str_replace('</p>','',$return);
                $after = $this->str_get_html($return);
                $shortdescription = $after->plaintext;
                $description = $after->find('div#productDescription',0)->innertext;
                $description = preg_replace('#(<[a-z ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $description);
                $shortdescription = $this->str_trim($shortdescription, 'WORDS', 140, '...');
            }
        }
        $returns = array(
            "description"=> trim($description),
            "short_description"=> trim($shortdescription)
        );
        return($returns);
    }

    public function geteditorialreview(){
        $htmls = $this->htmls;
        $result ='';
        if($htmls->find('div#editorialReviews') != false){
            $result = $htmls->find('div#editorialReviews',0)->outertext;
            $result = str_replace('<p','<div',$result);
            $result = str_replace('</p>','</div>',$result);
            $result = str_replace('<table align="center" border="0" class="data" width="100%">','<table align="center" border="0" style="display: block;overflow: auto;" width="100%">',$result);
        }
        $hasil['editorial_review'] = $result;
        return($hasil);
    }

    public function getdetail(){
        $feature ='';
        $detail ='';
        $category = '';
        $htmls = $this->htmls;
        if($htmls->find('div#productDetails') != false){
            $htmlnya = $htmls->find('div#productDetails',0);
            $htmlnya = $htmlnya->innertext;
            $details = $this->str_get_html($htmlnya);
            if($details->find('h3') != false){
                $h3 = $details->find('h3',0);
                $h3nya = $h3->outertext;
                $data = explode ($h3nya,$htmlnya);
                $feature = $data[1];
                $feature = str_replace('<ul>','<ul class="product-desc-list">',$feature);
                $detailnya = $data[0];
                $h2 = $details->find('h2',0);
                $h2nya = $h2->outertext;
                $data2 = explode($h2nya,$detailnya);
                $detail = $data2[1];
                $detail = str_replace('<ul>','<ul class="product-desc-list">',$detail);
                if($this->country == 'fr'){
                    $break = ' dans ';
                }else{
                    $break = ' in ';
                }
                $category = $this->lz_cutter($detail,$break,'</li>');
            }
        }
        $result = array(
            "feature" => trim($feature),
            "detail" => trim($detail),
            'category' => $category = trim($category)
        );
        return($result);
    }

    public function getreview(){
        $htmls = $this->htmls;
        $text = '';
        if($htmls->find('div#customerReviews') != false){
            $htmlnya = $htmls->find('div#customerReviews',0);
            $print = $htmlnya->innertext;
            $html = $this->str_get_html($print);
            $source = $html->find('p');
            $count = count($source);
            for ($i=1;$i<$count;$i++){
                $a = $source[$i];
                $text .= $a->outertext;
            }
        }
        $hasil['review'] = $text;
        return($hasil);
    }

    public function getsimilar(){
        $data = array();
        $return = array();
        $htmls = $this->htmls;
        if($htmls->find('div.productwidget') != false){
            $htmlnya = $htmls->find('div.productwidget',0);
            $html = $this->str_get_html($htmlnya);
            foreach($html->find('tr') as $e) {
                $data[] = $e->innertext;
            }
            foreach ($data as $img){
                $tr = $this->str_get_html($img);
                $c = $tr->find('a',0);
                $links = $c->href;
                $asins = explode('/',$links);
                $asin = $asins[3];
                $price = $tr->find('span.price',0)->plaintext;
                $z = $tr->find('img.sidebarproduct',0);
                $title = $z->alt;
                $img = $z->src;
                $img = str_replace(array('._SL75_.','jpg','png','http://ecx.images-amazon.com/images/I/'),'',$img);
                $return[] = array(
                    'asin' => $asin,
                    'title' => $title,
                    'image' => $img,
                    'price' => $price
                );
            }
        }
        $hasil['similars'] = $return;
        return($hasil);
    }

    function zm_get_source($url, $referer = 'http://www.google.com/firefox?client=firefox-a&rls=org.mozilla:fr:official', $ua = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.18) Gecko/20110614 Firefox/3.6.18') {
        if(function_exists('curl_exec')) {
            $curl = curl_init();
            $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
            $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $header[] = "Accept-Language: en-us,en;q=0.5";
            $header[] = "Pragma: ";
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, $ua);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_REFERER, $referer);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($curl);
            curl_close($curl);
        } else {
            ini_set('user_agent', $ua);
            $content = file_get_contents($url);
        }
        return $content;
    }

    function lz_cutter($content, $start, $end) {
        if($content && $start && $end) {
            $r = explode($start, $content);
            if (isset($r[1])) {
                $r = explode($end, $r[1]);
                return $r[0];
            }
            return false;
        }
        return false;
    }

    function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = 'UTF-8', $stripRN=true, $defaultBRText="\r\n", $defaultSpanText=" ")
    {
        $dom = new Htmldom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > 600000)
        {
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }

    function str_trim($string, $method = 'WORDS', $length = 25, $pattern = '...')
    {
        if(!is_numeric($length))
        {
            $length = 25;
        }

        if(strlen($string) <= $length)
        {
            return $string;
        }
        else
        {
            switch($method)
            {
                case 'CHARS':
                    return substr($string, 0, $length) . $pattern;
                    break;

                case 'WORDS':
                    if (strstr($string, ' ') == false)
                    {
                        return self::str_trim($string, 'CHARS', $length, $pattern);
                    }

                    $count = 0;
                    $truncated = '';
                    $word = explode(" ", $string);

                    foreach($word AS $single)
                    {
                        if($count < $length)
                        {
                            if(($count + strlen($single)) <= $length)
                            {
                                $truncated .= $single . ' ';
                                $count = $count + strlen($single);
                                $count++;
                            }
                            else if(($count + strlen($single)) >= $length)
                            {
                                break;
                            }
                        }
                    }

                    return rtrim($truncated) . $pattern;
                    break;
            }
        }
        return $string;
    }
}
