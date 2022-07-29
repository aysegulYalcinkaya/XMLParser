<?php

class ModaCizgiParser
{
    /*
     “product ws_code-> Group Code (if the product has no variants don't use group code)
    “product name” -> Product Name
    If “subproduct stock” is blank then “product stock” -> Quantity
    “product category_path” -> Category
    If “subproduct ws_code” is blank then “product ws_code” -> product Code
    “price_list” -> Price
    “price_list” ->List Price
    "detail" -> Full Description
    "product_link" -> Product Link
    “img_item” - > image1, “img_item” - > image2 …etc
    “brand” ->Brand
    "subproduct type1" -> Size
    "subproduct type2" -> Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE","LIST PRICE", "BRAND", "FULL DESCRIPTION", "PRODUCT CODE", "QUANTITY", "SIZE","COLOR" ,"DISCOUNTED PRICE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8"];
        $this->fieldNames = ["ws_code", "name", "category_path", "price_list","price_list", "brand", "detail", "ws_code", "stock"];
        $this->varianceFields = ["ws_code", "stock", "type1","type2"];

        $this->productRoot = "//products/product";
        $this->varianceRoot = "subproducts/subproduct";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.modacizgi.com/xml/?R=2859&K=aec2&Seo=1&Imgs=1&AltUrun=1&TamLink&Dislink');

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
            $c = count($this->fieldNames) - 2;
            if (count($variants) > 0) {
                foreach ($variants as $v) {
                    $value = array();
                    $value = $pvalue;
                    foreach ($this->varianceFields as $i => $query) {
                        $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);
                    }

                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    $value=Parser::setImages($value,$images,8);
                    fputcsv($file, $value);
                }
            } else {
                for ($i = 0; $i < count($this->varianceFields) - 2; $i++) {
                    $pvalue[$this->fieldHeaders[$c + $i + 2]] = "";
                }
                $pvalue["GROUP CODE"]="";
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                $pvalue=Parser::setImages($pvalue,$images,8);
                fputcsv($file, $pvalue);
            }

        }
        fclose($file);
    }
}