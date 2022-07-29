<?php

class BayichekichParser
{
    /*
     “SKU” -> Group Code
    “Name” -> Product Name
    “ProductCombination StockQuantity” -> Quantity
    “CategoryPath” -> Category
    “ProductCombination Gtin” -> product Code
    “Afiyat” -> Price
    “Price” ->List Price
    “PictureUrl” - > image1, “PictureUrl” - > image2 …etc
    “FullDescription” ->Full Description
    “ProductCombination Color” ->Color
    “ProductCombination ProductAttribute Value” ->Size
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION", "COLOR","SIZE", "PRODUCT CODE", "STOCK", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8", "IMAGE 9","DISCOUNTED PRICE"];
        $this->fieldNames = ["SKU", "Name", "Categories/Category/CategoryPath", "Afiyat", "Price", "FullDescription"];
        $this->varianceFields = ["Color", "ProductAttributes/ProductAttribute/Value","Gtin", "StockQuantity"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "ProductCombinations/ProductCombination";

        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.bayichekich.com/chekich-xml&kullanici_adi=abgooverseas&sifre=123456&key=746112c');

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
                $c=count($this->fieldNames);
                $images=Parser::getXPathDataMulti($xpath,"Pictures/Picture",$product);
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }

                        if ($images){
                            foreach ($images as $i=>$image) {
                                $value["IMAGE ".($i+1)]=Parser::getXPathData($xpath, "PictureUrl", $image);
                            }
                        }
                        for ($i=count($images);$i<9;$i++)
                            $value["IMAGE ".($i+1)]="";
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i=0;$i<count($this->varianceFields);$i++)
                        $pvalue[$this->fieldHeaders[$c+$i]]="";
                    if ($images){
                        foreach ($images as $i=>$image) {
                            $pvalue["IMAGE ".($i+1)]=Parser::getXPathData($xpath, "PictureUrl", $image);

                        }
                    }
                    for ($i=count($images);$i<9;$i++)
                        $pvalue["IMAGE ".($i+1)]="";
                    $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}