<?php

class MidyatGumusDunyasiParser
{
    /*
   “product_name” -> Product Name
    “Stok” -> Quantity
    “categories1” -> Category
    “model” -> product Code
    “special_price” -> Price (First remove "TL" then remove "." then replace "," with ".")
    “price” -> List Price
    “image” - > image1, “additional_images1” - > image2 …etc
    “description” ->Full Description
    please put this in the brand field "Midyat gumus Dunyasi" -> Brand
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "FULL DESCRIPTION", "QUANTITY", "PRICE", "LIST PRICE","IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4","IMAGE 5","IMAGE 6","IMAGE 7","IMAGE 8","IMAGE 9","IMAGE 10","BRAND", "DISCOUNTED PRICE"];
        $this->fieldNames = ["model","product_name", "categories1","description","Stok","special_price","price", "image","additional_images1","additional_images2","additional_images3", "additional_images4", "additional_images5", "additional_images6","additional_images7","additional_images8","additional_images9"];

        $this->productRoot = "//any_feed_pro_product_list/product";
         $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://midyatgumusdunyasi.com/index.php?route=feed/any_feed_pro&name=basit');
        $response=substr($response,strpos($response,'<?xml version="1.0" encoding="UTF-8"?>'));
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
                $pvalue["PRICE"]=str_replace(",",".",str_replace(".","",str_ireplace("TL","",$pvalue["PRICE"])));
                $pvalue["BRAND"]="Midyat Gumus Dunyasi";
                if ($pvalue["PRICE"]=="") $pvalue["PRICE"]=0;
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;

                fputcsv($file, $pvalue);
            }

        fclose($file);
    }
}