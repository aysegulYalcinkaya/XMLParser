<?php


class Parser
{
    static function getFieldData($product, $field)
    {
        $node = $product->getElementsByTagName($field);
        if (count($node)) return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", str_replace('"',"'",htmlspecialchars_decode(strip_tags(trim($node[0]->textContent),"<ul><ol><li>")))); else
            return "";
    }

    static function getXPathData($xpath, $query,$node)
    {
        $childNode = $xpath->query($query,$node);

        if (count($childNode)>0) return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n",  str_replace('"',"'",strip_tags(htmlspecialchars_decode(trim($childNode[0]->textContent))))); else
            return "";
    }
    static function getXPathDataMulti($xpath, $query,$node)
    {
        $childNode = $xpath->query($query,$node);
        if (count($childNode)>0) return $childNode; else
            return [];
    }

    static function curlRequest($url){
        $curl = curl_init();

        curl_setopt_array($curl,
            array(CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array('Cookie: PHPSESSID=pgv0ntdihdb77v4a49lohgejb4'),
                CURLOPT_SSL_VERIFYPEER=>false,
                CURLOPT_SSL_VERIFYHOST=>false));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    static function setImages($values,$images,$maxNumberOfImages){
        if ($images) {
            foreach ($images as $i=>$image) {
                if ($i==$maxNumberOfImages) break;
                $values["IMAGE ".($i+1)] = $image->textContent;
            }
        }

        for ($i=count($images);$i<$maxNumberOfImages;$i++)
            $values["IMAGE ".($i+1)]="";
        return $values;
    }
}