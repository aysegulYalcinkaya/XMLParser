<?php

class TonnyMoodParser
{

    /*
    " ProductCombination\ SKU" -> Group Code
    " Product\ Name" -> Product Name
    “ProductCombination\ StockQuantity" -> Quantity
    " Category\ CategoryPath" -> Category
    “ProductCombination\ Gtin" -> product Code
    “Product\ Price" -> Price
    “Product\ OldPrice" -> List Price
    " Picture\ PictureUrl" - > image1, " Picture\ PictureUrl" - > image2 …etc
    " Product\ FullDescription" ->Full Description
    " Manufacturer\ Name" -> Brand
    “ProductCombination\ProductAttribute\Value” -> Size
    “ProductCombination\Renk” -> Color
     */
    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $varianceFields;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "BRAND", "PRICE", "LIST PRICE", "DESCRIPTION", "PRODUCT CODE", "STOCK", "SIZE", "COLOR", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "DISCOUNTED PRICE"];
        $this->fieldNames = ["SKU", "Name", "Categories/Category/CategoryPath", "Manufacturers/Manufacturer/Name", "Price", "OldPrice", "FullDescription"];
        $this->varianceFields = ["Gtin", "StockQuantity", "ProductAttributes/ProductAttribute/Name[text()='Beden']/../Value", "Renk"];

        $this->varianceRoot = "ProductCombinations/ProductCombination";
        $this->productRoot = "//Products/Product";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $url = 'https://bayiayakkabi.com/export.xml';

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);


        $response = Parser::curlRequest($url);

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);


        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }
            $variances = $xpath->query($this->varianceRoot, $product);
            $c = count($this->fieldNames);

            if (count($variances) > 0) {
                foreach ($variances as $v) {
                    $value = $pvalue;
                    foreach ($this->varianceFields as $i => $query) {
                        $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);

                    }

                    $images = $xpath->query("Pictures/Picture/PictureUrl", $product);
                    $value = Parser::setImages($value, $images, 5);
                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;

                    fputcsv($file, $value);
                }
            } else {
                $images = $xpath->query("Pictures/Picture/PictureUrl", $product);
                $pvalue = Parser::setImages($pvalue, $images, 5);
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                fputcsv($file, $pvalue);
            }
        }

        fclose($file);
    }

}