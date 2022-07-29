<?php

class CicibebeAyakkabiParser
{
    /*
     “Product Sku -> Group Code
    “Name” -> Product Name
    “Combination StockQuantity” -> Quantity
    “category path” -> Category
    “Combination Sku ” -> product Code
    “Product Price” -> Price
    “Product OldPrice” ->List Price
    “Picture path” - > image1, “Picture path” - > image2 …etc
    “FullDescription” ->Full Description
    “Specification Name="Renk" Value ” ->Color
    “Attribute Value” ->Size
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;

    private $variantNames;
    private $variantRoot;
    private $productRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE",  "FULL DESCRIPTION", "COLOR", "PRODUCT CODE", "QUANTITY", "SIZE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "DISCOUNTED PRICE"];
        $this->fieldNames = ["@Sku", "@Name", "Categories/Category[@Path!='']/@Path", "@Price", "@OldPrice",  "@FullDescription", "Specifications/Specification[@Name='Renk']/@Value"];
        $this->variantNames = ["@Sku", "@StockQuantity", "Attributes/Attribute[@Name='Numara']/@Value"];

        $this->productRoot = "//Products/Product";
        $this->variantRoot = "Combinations/Combination";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.cicibebeayakkabi.com.tr/FaprikaXml/RSFJPQ/1/');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);

        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }
            $pvalue["PRICE"]=str_replace(",",".",$pvalue["PRICE"]);
            $pvalue["LIST PRICE"]=str_replace(",",".",$pvalue["LIST PRICE"]);
            $images = Parser::getXPathDataMulti($xpath, "Pictures/Picture/@Path", $product);

            $variants = $xpath->query($this->variantRoot, $product);
            $c = count($this->fieldNames);
            if (count($variants) > 0) {
                foreach ($variants as $v) {
                    $value = array();
                    $value = $pvalue;
                    foreach ($this->variantNames as $i => $variantName) {
                        $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $variantName, $v);
                    }
                    $value = Parser::setImages($value, $images, 5);
                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    fputcsv($file, $value);
                }
            } else {
                for ($i = 0; $i < count($this->variantNames); $i++) $pvalue[$this->fieldHeaders[$c + $i]] = "";
                $pvalue = Parser::setImages($pvalue, $images, 5);
                fputcsv($file, $pvalue);
            }
        }
        fclose($file);
    }
}