<?php

class ZavansaParser
{

    /*
   " Product\Product_code" -> Group Code (if it has no variants please leave it empty)
    " Product\ Name" -> Product Name
    If “variant\quantity" is blank then use “Product\ Stock” -> Quantity
    Please join " Product\ mainCategory" with " Product\ Category" with “Product\ subCategory” by having a slash between them -> Category
    If “variant\ productCode" is blank then use " Product\Product_code" -> product Code
    Please multiply " Product\ fiyat" by " Product\ Tax" -> Price
    Please multiply " Product\ fiyat" by " Product\ Tax" -> List Price
    " Product\Image1" - > image1, " Product\Image2" - > image2 …etc
    " Product\ Description" ->Full Description
    Please make this " Zavansa" as a brand -> Brand
    If “variant\ spec name” = “Beden” or =“Numara” then “spec” = size -> Size
    If “variant\ spec name” = “RENK” then “spec” = color -> Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "BRAND", "PRICE", "LIST PRICE", "DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "PRODUCT CODE", "STOCK", "SIZE", "COLOR", "DISCOUNTED PRICE"];
        $this->fieldNames = ["Product_code", "Name", "mainCategory", "Brand", "fiyat", "fiyat", "Description","Image1","Image2","Image3","Image4","Image5","Product_code","Stock"];
        $this->varianceFields = ["productCode", "quantity", "spec[@name='Beden' or @name='Numara' or @name='BEDEN']", "spec[@name='Renk' or  @name='RENK']"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "variants/variant";

        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $url = 'http://cdn1.xmlbankasi.com/p1/brrasxiqdvom/image/data/xml/zavansa1.xml';

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);


        //$response = Parser::curlRequest($url);
$response=file_get_contents("Parsers/zavansa1.xml");
        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);


        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }
            $pvalue["BRAND"]="Zavansa";
            $cat=Parser::getXPathData($xpath, "category", $product);
            $subcat=Parser::getXPathData($xpath, "subCategory", $product);

            $tax=Parser::getXPathData($xpath, "Tax", $product);
            $pvalue["PRICE"]=$pvalue["PRICE"]*(1+$tax/100);
            $pvalue["LIST PRICE"]=$pvalue["PRICE"];
            if ($cat)
                $pvalue["CATEGORY"].=" / ".$cat ;
            if ($subcat)
                $pvalue["CATEGORY"].=" / ".$subcat ;


            $variances = $xpath->query($this->varianceRoot, $product);
            $c = count($this->fieldNames);

            if (count($variances) > 0) {
                foreach ($variances as $v) {
                    $value = $pvalue;
                    foreach ($this->varianceFields as $i => $query) {
                        $value[$this->fieldHeaders[$c + $i-2]] = Parser::getXPathData($xpath, $query, $v);

                    }

                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;

                    fputcsv($file, $value);
                }
            } else {
               $pvalue["GROUP CODE"]="";
                $pvalue["SIZE"]="";
                $pvalue["COLOR"]="";
                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                fputcsv($file, $pvalue);
            }
        }

        fclose($file);
    }

}