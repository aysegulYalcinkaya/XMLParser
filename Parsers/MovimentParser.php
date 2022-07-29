<?php

class MovimentParser
{

    /*
   " product\ sku" -> Group Code
    " product\ title" -> Product Name
    “variant\ variantStock" -> Quantity
    " product\ categoryPath" -> Category
    “variant\ variantSku" -> product Code
    “variant\ variantCostPrice" -> Price
    “variant\ variantSalePrice" -> List Price
    " product\ src" - > image1, " product\ src" - > image2 …etc
    " product\ detail" ->Full Description
    Please make this “Moviment” as -> Brand
    If “variant\ label" = “Beden” -> Size
    " product\ attributeValue" -> Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY","BRAND", "DESCRIPTION", "PRODUCT CODE","PRICE", "LIST PRICE", "STOCK", "SIZE", "COLOR", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "DISCOUNTED PRICE"];
        $this->fieldNames = ["sku", "title", "categoryPath", "brand","detail"];
        $this->varianceFields = ["variantSku", "variantCostPrice","variantSalePrice","variantStock", "attributes/attribute/label[text()='Beden']/../value", "attributes/attribute/label[text()='Renk']/../value"];

        $this->productRoot = "//products/product";
        $this->varianceRoot = "variants/variant";

        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $url = 'https://moviment.com.tr/xml/products/merchant/107?pattern=standart&lang=tr';

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

                    $images = $xpath->query("images/image/src", $product);
                    $value = Parser::setImages($value, $images, 6);
                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;

                    fputcsv($file, $value);
                }
            } else {
                $images = $xpath->query("images/image/src", $product);
                $pvalue = Parser::setImages($pvalue, $images, 6);
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                fputcsv($file, $pvalue);
            }
        }

        fclose($file);
    }

}