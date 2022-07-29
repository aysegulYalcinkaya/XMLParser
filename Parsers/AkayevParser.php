<?php

class AkayevParser
{
    /*
     “Name” -> Product Name
     “Product_code” -> product Code
     “Stock” -> Quantity
     “category” -> Category
     “Price” -> Price
     “Brand” -> Brand
     “Image1” - > image1, “Image2” - > image2 …etc
     “Description” ->Full Description
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
        $this->fieldNames = ["Product_code", "Name", "category", "Brand", "Stock", "Price", "Description", "Image1", "Image2", "Image3", "Image4", "Image5"];
        $this->productRoot = "//Products/Product";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.akayev.com.tr/bayiyeni');
        $this->writeFile($response,$this->filename,$this->fieldNames,$this->productRoot);

    }

    private function writeFile($response,$filename,$fieldNames,$productRoot)
    {
        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($productRoot) as $product) {
            $value = array();
            foreach ($fieldNames as $i=>$fieldName) {
                $value[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }
            $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
            fputcsv($file, $value);
        }
        fclose($file);
    }
}