<?php
require_once 'Parser.php';

class FularcimParser
{

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "BRAND", "DESCRIPTION", "BARCODE", "STOCK", "STATUS", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "DISCOUNTED PRICE"];
        $this->fieldNames = ["productCode", "name", "category", "buyingPrice", "price", "brand", "detail", "barcode", "quantity", "active", "image1", "image2", "image3", "image4", "image5"];

        $this->productRoot = "//products/product";
        $this->discount=(100-$discount)/100;

    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.fularcim.com/export/360b34f85dd6403fd812433dd59d48c8JMYKrcEsNmmOeIhADg==');

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
            $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
            fputcsv($file, $value);
        }
        fclose($file);
    }

}