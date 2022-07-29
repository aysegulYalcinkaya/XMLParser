<?php
require_once 'Parser.php';

class LactoneParser
{

    /*
     “Name” -> Product Name
    “Quantity” -> Quantity
    “Category” -> Category
    “StockCode” -> product Code
    “SalesPrice” -> Price
    “SalesPrice” ->List Price
    “Image” - > image1, “Image” - > image2 …etc
    "ProductUrl" -> Link
    “Description” ->Full Description
    “Brand” ->Brand
     */
    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "URL", "PRODUCT NAME", "CATEGORY", "BRAND", "STOCK", "PRICE","LIST PRICE", "FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "DISCOUNTED PRICE"];
        $this->fieldNames = ["StockCode", "ProductUrl", "Name", "Category", "Brand", "Quantity", "SalesPrice","SalesPrice", "Description"];
        $this->productRoot = "//Products/Product";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.lactone.com.tr/xml-abgo-overseas');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $value = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $value[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }
            $imagesNode = Parser::getXPathDataMulti($xpath, "Images/Image", $product);
            $value = Parser::setImages($value, $imagesNode, 5);
            $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
            fputcsv($file, $value);
        }
        fclose($file);
    }

}