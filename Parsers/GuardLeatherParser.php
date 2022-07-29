<?php

class GuardLeatherParser
{
    /*
     “product ws_code-> Group Code (if the product has no variants don't use group code)
    “product name” -> Product Name
    If “subproduct stock” is blank then “product stock” -> Quantity
    “product category_path” -> Category
    If “subproduct ws_code” is blank then “product ws_code” -> product Code
    If “subproduct price_special” is blank then “product price_special” -> Price
    “product price_special_vat_included” ->List Price
    “img_item” - > image1, “img_item” - > image2 …etc
    “detail” ->Full Description
    “brand” ->Brand
    "subproduct type2" -> Size
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "LIST PRICE", "BRAND", "FULL DESCRIPTION", "PRODUCT CODE", "QUANTITY", "PRICE", "SIZE", "DISCOUNTED PRICE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5"];
        $this->fieldNames = ["ws_code", "name", "category_path", "price_special_vat_included", "brand", "detail", "ws_code", "stock", "price_special"];
        $this->varianceFields = ["ws_code", "stock", "price_special", "type2"];

        $this->productRoot = "//products/product";
        $this->varianceRoot = "subproducts/subproduct";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.guardleather.com/xml/?R=2818&K=c29c&Seo=1&Imgs=1&AltUrun=1&TamLink&Dislink');

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
            $images=Parser::getXPathDataMulti($xpath,"images/img_item",$product);

            $variants = $xpath->query($this->varianceRoot, $product);
            $c = count($this->fieldNames) - 3;
            if (count($variants) > 0) {
                foreach ($variants as $v) {
                    $value = array();
                    $value = $pvalue;
                    foreach ($this->varianceFields as $i => $query) {
                        $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);
                    }

                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    $value=Parser::setImages($value,$images,5);
                    fputcsv($file, $value);
                }
            } else {
                for ($i = 0; $i < count($this->varianceFields) - 3; $i++) {
                    $pvalue[$this->fieldHeaders[$c + $i + 3]] = "";
                }
                $pvalue["GROUP CODE"]="";
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                $pvalue=Parser::setImages($pvalue,$images,5);
                fputcsv($file, $pvalue);
            }

        }
        fclose($file);
    }
}