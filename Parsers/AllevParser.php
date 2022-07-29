<?php

class AllevParser
{

    /*
        “urunadi” -> Product Name
        “stok” -> Quantity
        “CategoryTree” -> Category
        “barkod” -> product Code
        “alisfiyat” -> Price
        “marka” -> Brand
        “bresim” - > image1, “bresim2” - > image2 …etc
        “aciklama” ->Full Description
     */
    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "BRAND", "QUANTITY", "PRICE", "FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["barkod", "urunadi", "CategoryTree", "marka", "stok", "alisfiyat", "aciklama", "bresim", "bresim2", "bresim3", "bresim4", "bresim5"];
        $this->productRoot = "//urunler/urun";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.allev.com/outputxml/index.php?xml_service_id=9');

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