<?php

class RapellinParser
{
    /*
    "urun_gtin" -> Group Code
    “urun\urun_ad” -> Product Name
    “varyasyon\stok" -> Quantity
    “urun\urun_kategori_path" -> Category
    if “varyasyon\gtin” is blank use “varyasyon\stok_kod” -> product Code
    “ürün_fiyat_son_kullanıcı” -> Price
    “urun_fiyat_site” ->List Price
    “urun_resim1” - > image1, “urun_resim2” - > image2 …etc
    “urun_aciklama” ->Full Description
    "urun_marka_ad" -> Brand
    “varyasyon\ var1 baslik="Beden" varyasyon" -> Size
    “urun_url” > product Link
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION","BRAND", "PRODUCT LINK","IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","SIZE", "PRODUCT CODE", "QUANTITY","DISCOUNTED PRICE"];
        $this->fieldNames = ["urun_gtin", "urun_ad", "urun_kategori_path", "urun_fiyat_son_kullanici", "urun_fiyat_site", "urun_aciklama", "urun_marka_ad","urun_url","urun_resim1","urun_resim2","urun_resim3","urun_resim4","urun_resim5"];
        $this->varianceFields = ["var1[@baslik='Beden']/@varyasyon","gtin", "stok"];

        $this->productRoot = "//urunler/urun";
        $this->varianceRoot = "urun_varyasyonlari/varyasyon";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://www.rapellin.com/xml.php?c=shopphp&xmlc=d8cf8a0327');

        $doc = new DOMDocument();
        $doc->loadXML(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $response));
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }
            if ($pvalue["GROUP CODE"] != "") {
                $variants=$xpath->query($this->varianceRoot, $product);
                $c=count($this->fieldNames);
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        if ($value["PRODUCT CODE"]==""){
                            $value["PRODUCT CODE"]=Parser::getXPathData($xpath, "stok_kod", $v);
                        }
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i=0;$i<count($this->varianceFields);$i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}