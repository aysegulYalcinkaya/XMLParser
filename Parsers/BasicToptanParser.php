<?php

class BasicToptanParser
{
    /*
     “ProductCode” -> Group Code
    “ProductName” -> Product Name
    “Quantity” -> Quantity
    “Category” -> Category
    “Barcode” -> product Code
    “SalePrice” -> Price ==> Price -> Price
    “SalePrice” ->List Price
    “Image1” - > image1, “Image2” - > image2 …etc
    “Description” ->Full Description
    “ColorValue” ->Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION", "COLOR", "PRODUCT CODE", "STOCK", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8","DISCOUNTED PRICE"];
        $this->fieldNames = ["ProductCode", "ProductName", "Category", "Price", "SalePrice", "Description"];
        $this->varianceFields = ["ColorValue", "Size/Barcode", "Size/Quantity"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "Colors/Color";
        $this->discount=(100-$discount)/100;

    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.basictoptan.com/standart_products.xml');

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
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }

                        $images=Parser::getXPathDataMulti($xpath,"Images/*",$v);
                        $value=Parser::setImages($value,$images,8);
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for($i=0;$i<count($this->varianceFields);$i++)
                        $pvalue[$this->fieldHeaders[$c+$i]]="";

                    $pvalue=Parser::setImages($pvalue,array(),8);
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }

}