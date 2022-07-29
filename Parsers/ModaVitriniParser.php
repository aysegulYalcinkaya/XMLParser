<?php

class ModaVitriniParser
{

    /*
    " Product \ ws_code" -> Group Code
    “Product\ Name” -> Product Name
    “subproduct\ stock " -> Quantity
    “Product\ category_path” -> Category
    “subproduct\ ws_code " -> product Code
    " Product \ price_special_vat_included " -> Price
    " Product \ price_list_vat_included" -> List Price
    "Product\ img_item" - > image1, " Product\ img_item " - > image2 …etc
    "Product\ details " ->Full Description
    Please make this “Fashion Showcase” as -> Brand
    “subproduct \ type2 " -> Size
    “subproduct \ type1 " -> Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "DESCRIPTION", "PRODUCT CODE", "STOCK", "SIZE", "COLOR", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "DISCOUNTED PRICE"];
        $this->fieldNames = ["ws_code", "name", "category_path", "price_special_vat_included", "price_list_vat_included", "details"];
        $this->varianceFields = ["ws_code", "stock", "type2", "type1"];

        $this->productRoot = "//products/product";
        $this->varianceRoot = "subproducts/subproduct";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $url = 'http://modavitrini.com/connectprof/b1fb518_modavitrinicom';

        $file = fopen($this->filename, "w");
        fputcsv($file, array_merge(array("BRAND"),$this->fieldHeaders));


        $response = Parser::curlRequest($url);

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);


        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            $pvalue["BRAND"]="Fashion Showcase";
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

                    $images = $xpath->query("img_item", $product);
                    $value = Parser::setImages($value, $images, 5);
                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;

                    fputcsv($file, $value);
                }
            } else {
                $images = $xpath->query("img_item", $product);
                $pvalue = Parser::setImages($pvalue, $images, 5);
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                fputcsv($file, $pvalue);
            }
        }

        fclose($file);
    }

}