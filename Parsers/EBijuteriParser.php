<?php

class EBijuteriParser
{

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "BRAND","DESCRIPTION","COLOR","BARCODE","QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6","DISCOUNTED PRICE"];
        $this->fieldNames = ["stok_kodu", "adi", "kategori", "fiyat/bayi_fiyati","fiyat/son_kullanici", "marka","aciklama","filtreler/filtre[name='Renk']/value","barcode","miktar"];

        $this->productRoot="//Urunler/Urun";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://xml.ebijuteri.com/api/xml/61127a0611c66a2add11dc92?format=old');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $value = array();
            foreach ($this->fieldNames as $i=>$query) {
                $value[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $query, $product);
            }
            $images=Parser::getXPathDataMulti($xpath,"resim/*",$product);
            $value=Parser::setImages($value,$images,6);
            $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
            fputcsv($file, $value);
        }
        fclose($file);
    }

}