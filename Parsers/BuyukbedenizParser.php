<?php

class BuyukbedenizParser
{
    /*
     “Product ws_code -> Group Code
    “Name” -> Product Name
    “subproduct Stock” -> Quantity
    “category_path” -> Category
    “subproduct ws_code” -> product Code
    “price_special_vat_included” -> Price
    “price_special_vat_included” ->List Price
    “img_item” - > image1, “img_item” - > image2 …etc
    “detail” ->Full Description
    “subproduct type1 " ” ->Color

    “subproduct type2” ->Size
    “brand” -> Brand
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION","BRAND", "COLOR","SIZE", "PRODUCT CODE", "QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8", "IMAGE 9","DISCOUNTED PRICE"];
        $this->fieldNames = ["ws_code", "name", "category_path", "price_special_vat_included", "price_special_vat_included", "detail","brand"];
        $this->varianceFields = ["type1", "type2","ws_code", "stock"];

        $this->productRoot = "//products/product";
        $this->varianceRoot = "subproducts/subproduct";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.buyukbedeniz.com/xml/?R=27828&K=2edb&Seo=1&Imgs=1&AltUrun=1&TamLink');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }
            if ($pvalue["GROUP CODE"] != "") {
                $variants=$xpath->query($this->varianceRoot, $product);
                $images=Parser::getXPathDataMulti($xpath,"images/img_item",$product);
                $c=count($this->fieldNames);
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        $variantimages=Parser::getXPathDataMulti($xpath,"images/img_item",$v);

                        if (count($variantimages)>0)
                            $images=$variantimages;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }

                        $value=Parser::setImages($value,$images,9);
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{

                    for ($i=0;$i<count($this->varianceFields);$i++)
                        $pvalue[$this->fieldHeaders[$c+$i]]="";
                    $pvalue=Parser::setImages($pvalue,$images,9);
                    $pvalue["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}