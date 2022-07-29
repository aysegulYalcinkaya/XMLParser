<?php

class NevresimDunyasiParser
{
    /*
     “product Name” -> Product Name
    “Stock” -> Quantity
    “category” -> Category
    “Product_code” -> product Code
    “Price” -> Price
    “Price” ->List  Price
    "Description" -> Full Description
    “Image1” - > image1, “Image2” - > image2 …etc
    “Brand” ->Brand
     */


    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "BRAND","QUANTITY", "PRICE", "LIST PRICE","FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["Product_code", "Name", "category", "Brand","Stock", "Price","Price", "Description", "Image1", "Image2", "Image3", "Image4", "Image5"];

        $this->productRoot = "//Products/Product";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://nevresimdunyasi.xmlbankasi.com/image/data/xml/urunlerkdvdahil.xml');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }
            $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
            fputcsv($file, $pvalue);
        }

        fclose($file);
    }
}