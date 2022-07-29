<?php

class DegerindenAlParser
{
    /*
     “sku -> Group Code
    “urun_adi” -> Product Name
    If “secenek_adet” is blank then “adet” -> Quantity
    “kategori_adi” -> Category
    If “varyant_barkod” is blank then “urun_kodu” -> product Code
    “fiyat” -> Price
    “fiyat” ->List Price
    “resim1” - > image1, “resim2” - > image2 …etc
    “aciklama” ->Full Description
    “marka ” ->Brand
    "url" -> Product Link
    If "secenek_adi = Beden" -> Size
    If "secenek_adi = Renk" - Color
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "BRAND", "CATEGORY", "FULL DESCRIPTION", "URL", "PRICE", "LIST PRICE","COLOR", "QUANTITY", "PRODUCT CODE", "SIZE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8", "DISCOUNTED PRICE"];
        $this->fieldNames = ["sku", "urun_adi", "marka", "kategoriler/kategori_adi", "aciklama", "url", "fiyat", "fiyat", "secenekler/secenek[secenek_adi='Renk']/secenek_degeri","adet", "urun_kodu"];
        $this->varianceFields = ["secenek_adet", "varyant_barkod", "secenek_degeri"];

        $this->productRoot = "//products/urun";
        $this->varianceRoot = "secenekler/secenek";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {
      /*  $urls = ['https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadin',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadin-1',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadin-2',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadin-3',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadi-5',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadi-6',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadi-7',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadi-8',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadi-9',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-kadi-999',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=999-petshop',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=wnt-01-ev-yasam',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=wnt-01-hediyelik',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=wnt-01-icgiyim-plaj',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=wnt-01-buyuk-beden'];*/
        $urls=['https://www.degerindenal.com/index.php?route=extension/module/xml2&name=ayakakbi',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=enstruman',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=Giyim',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=ic-giyim-2022',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=petshop2022',
            'https://www.degerindenal.com/index.php?route=extension/module/xml2&name=erkek-giyim-2022'];

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);

        foreach ($urls as $url) {

            $response = Parser::curlRequest($url);

            $doc = new DOMDocument();
            $doc->loadXML($response);
            $xpath = new DOMXPath($doc);

            foreach ($xpath->query($this->productRoot) as $product) {
                $pvalue = array();
                foreach ($this->fieldNames as $i => $fieldName) {
                    $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName,$product);
                }
                $images = Parser::getXPathDataMulti($xpath, "resim1", $product);

                $variants = $xpath->query($this->varianceRoot, $product);
                $c = count($this->fieldNames) - 2;
                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $value = array();
                        $value = $pvalue;
                        $secenekAdi = Parser::getXPathData($xpath, "secenek_adi", $v);
                        if ($secenekAdi!="Renk" && $secenekAdi!="Renk Seçiniz"){
                            foreach ($this->varianceFields as $i => $query) {
                                $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);
                            }
                            switch ($secenekAdi) {
                                case "Beden":
                                case "bedenn":
                                case "Numara":
                                case "Beden Seçiniz":
                                    break;
                                case "Beden - Renk":
                                    $options = preg_split("/ - /", $value["SIZE"]);
                                    $value["SIZE"] = trim($options[0]);
                                    $value["COLOR"] = trim($options[1]);
                                    break;
                                default:
                                    echo $secenekAdi . "\n";
                                    $value["SIZE"] = "";
                            }
                            $value = Parser::setImages($value, $images, 8);
                            $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                            if ($value["PRODUCT CODE"]=="0" || $value["PRODUCT CODE"]=="" ) $value["PRODUCT CODE"]=Parser::getXPathData($xpath, "urun_id",$product);
                            if ($value["COLOR"]){
                                $value["PRODUCT CODE"]=$value["PRODUCT CODE"]."-".$value["COLOR"];
                            }
                            if ($value["SIZE"]){
                                $value["PRODUCT CODE"]=$value["PRODUCT CODE"]."-".$value["SIZE"];
                            }

                            fputcsv($file, $value);
                        }

                    }
                } else {
                    for ($i = 0; $i < count($this->varianceFields) - 2; $i++) {
                        $pvalue[$this->fieldHeaders[$c + $i + 2]] = "";
                    }
                    $pvalue = Parser::setImages($pvalue, $images, 8);
                    $pvalue["PRODUCT CODE"]=Parser::getXPathData($xpath, "urun_id",$product);
                    $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                    fputcsv($file, $pvalue);
                }

            }

        }
        fclose($file);
    }
}