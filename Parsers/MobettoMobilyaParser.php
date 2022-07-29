<?php

class MobettoMobilyaParser
{
    /*
     “rootlabel” -> Product Name
    “status” -> Quantity
    “Category” -> Category
    “mainCategory” -> product Code
    “price5” -> Price
    “price1” ->List Price
    “picture1Path” - > image1, “picture2Path” - > image2 …etc
    "productLink" -> Link
    “details” ->Full Description
    “brand” ->Brand
    "label"-> Parts
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "BRAND", "QUANTITY", "PRICE","LIST PRICE","URL", "FULL DESCRIPTION", "PARTS","IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","IMAGE 6","IMAGE 7","IMAGE 8","IMAGE 9","DISCOUNTED PRICE"];
        $this->fieldNames = ["mainCategory", "label", "category", "brand", "status", "price5", "price1", "productLink","details","label","picture","picture1Path","picture2Path","picture3Path","picture4Path","picture5Path","picture6Path","picture7Path","picture8Path"];
        $this->productRoot = "//root/item";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://www.mobetto.com/index.php?do=catalog/output&pCode=1488209273');

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