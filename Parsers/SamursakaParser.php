<?php

class SamursakaParser
{
    /*
     “urun urun_ad” -> Product Name
    “urun urun_stok” -> Quantity
    “urun_kategori_ad” -> Category
    “urun_gtin” -> product Code
    “urun_fiyat” -> Price
    “urun_fiyat_piyasa” ->List Price
    "urun_aciklama" -> Full Description
    "urun_url" -> Product Link
    “urun_resim1” - > image1, “urun_resim2” - > image2 …etc
    “urun_marka_ad" ->Brand
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "QUANTITY", "PRICE", "LIST PRICE", "FULL DESCRIPTION", "PRODUCT LINK","BRAND","IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["urun_gtin", "urun_ad", "urun_kategori_ad", "urun_stok", "urun_fiyat" , "urun_fiyat_piyasa","urun_aciklama","urun_url" ,"urun_marka_ad","urun_resim1", "urun_resim2", "urun_resim3", "urun_resim4", "urun_resim5"];
        $this->productRoot = "//urunler/urun";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.samursaka.com/xml.php?c=shopphp&xmlc=973fbfbe49&username=m.aldemir@abgooverseas.com&password=123456Douj');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $value = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $value[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }
            $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
            fputcsv($file, $value);
        }
        fclose($file);
    }
}