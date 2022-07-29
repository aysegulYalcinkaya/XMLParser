<?php

class CosstaParser
{
    /*
     * "UrunKartiID" -> Group Code (if the product has no variants don't use group code)
        “UrunAdi” -> Product Name
        “UrunSecenek/Secenek/StokAdedi” -> Quantity
        “KategoriTree” -> Category
        “UrunSecenek/Secenek/StokKodu” -> product Code
        “UrunSecenek/Secenek/AlisFiyati” -> Price (First remove "TL" then remove "." then replace "," with ".")
        “Resim” - > image1, “Resim” - > image2 …etc
        “Aciklama” ->Full Description
        "Marka" -> Brand
        "UrunUrl" -> Product Link
        If "UrunSecenek/Secenek/EkSecenekOzellik/Ozellik/_Tanim" = "Beden" -> Size
        If "UrunSecenek/Secenek/EkSecenekOzellik/Ozellik/_Tanim" = "Renk" or "Kod" -> Color
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY",  "BRAND", "FULL DESCRIPTION","PRODUCT LINK", "PRODUCT CODE", "QUANTITY", "PRICE", "SIZE", "COLOR","DISCOUNTED PRICE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5"];
        $this->fieldNames = ["UrunKartiID", "UrunAdi", "KategoriTree", "Marka", "Aciklama","UrunUrl"];
        $this->varianceFields = ["StokKodu", "StokAdedi", "AlisFiyati", "EkSecenekOzellik/Ozellik[@Tanim='Beden']","EkSecenekOzellik/Ozellik[@Tanim='Renk' or @Tanim='Kod']"];

        $this->productRoot = "//Root/Urunler/Urun";
        $this->varianceRoot = "UrunSecenek/Secenek";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {
        $doc = new DOMDocument();
        $response = Parser::curlRequest('https://www.cossta.com.tr/TicimaxXml/08102E345DB14BD08DDA3FF02D4FB184/');
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }
            $images=Parser::getXPathDataMulti($xpath,"Resimler/Resim",$product);

            $variants = $xpath->query($this->varianceRoot, $product);
            $c=count($this->fieldNames);
            if (count($variants) > 0) {
                foreach ($variants as $v) {
                    $value = array();
                    $value = $pvalue;
                    foreach ($this->varianceFields as $i => $query) {
                        $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                    }
                    $value["PRICE"]=str_replace(",",".",str_replace(".","",str_ireplace("TL","",$value["PRICE"])));
                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    $value=Parser::setImages($value,$images,5);
                    fputcsv($file, $value);
                }
            } else {
                $pvalue["GROUP CODE"]="";
               // $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                $pvalue=Parser::setImages($pvalue,$images,5);
                fputcsv($file, $pvalue);
            }

        }
        fclose($file);
    }
}