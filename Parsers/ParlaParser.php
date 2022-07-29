<?php

class ParlaParser
{
    /*
   "Product\ Product_code" -> Group Code
    “Product\ Name” -> Product Name
    If “variants\ variant\ quantity" is blank use "Product\ Stock" -> Quantity
    Join “mainCategory” to “category” by having space between them -> Category
    If “variants\ variant\ barcode" is blank use "Product\ Barkod" -> product Code
    "Product\ Price" -> Price
    "Product\ Image1" - > image1, "Product\ Image2" - > image2 …etc
    "Product\ Description" ->Full Description
    "Product\ Brand" -> Brand
    If “variants\ variant\spec name="Beden" or “Numara" -> Size
    “variants\ variant\spec name="RENK" -> Color
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "FULL DESCRIPTION", "BRAND", "PRICE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "PRODUCT CODE","QUANTITY","SIZE","COLOR",   "DISCOUNTED PRICE"];
        $this->fieldNames = ["Product_code", "Name", "MainCategory", "Description", "Brand", "Price","Image1","Image2","Image3","Image4","Image5","Barkod","Stock"];
        $this->varianceFields = ["barcode","quantity","spec[@name='BEDEN']","spec[@name='RENK']"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "variants/variant";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://cdn1.xmlbankasi.com/p1/ecxqwlmentmf/image/data/xml/parlastore.xml');

        $doc = new DOMDocument();
        $doc->loadXML(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $response));
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }

                $pvalue["CATEGORY"].=" ".Parser::getFieldData($product,"category");


            if ($pvalue["GROUP CODE"] != "" and strtoupper(substr($pvalue["GROUP CODE"],0,3))!="STK") {
                $variants = $xpath->query($this->varianceRoot, $product);
                $c = count($this->fieldNames)-2;
                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $variant_value = array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i => $query) {
                            $variant_value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        if ($variant_value["PRODUCT CODE"]){
                            $value["PRODUCT CODE"]=$variant_value["PRODUCT CODE"];
                        }
                        if ($variant_value["QUANTITY"]){
                            $value["QUANTITY"]=$variant_value["QUANTITY"];
                        }
                        $value["SIZE"]=$variant_value["SIZE"];
                        $value["COLOR"]=$variant_value["COLOR"];

                        $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                        if ($value["SIZE"]!="" || $value["COLOR"]!="") {
                            fputcsv($file, $value);
                        }
                    }
                } else {
                    for ($i = 2; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c+ $i]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}