<?php

class GozdeMobilyaParser
{
    /*
     “Product Product_code"-> Group Code
    “Name” -> Product Name
    If “variant quantity” is blank then “Product Stock” -> Quantity
    “Product category” -> Category
    If “variant barcode” is blank then “ProductProduct_code” Then you will find blanks also so fill them with "variant productCode"-> product Code
    “Product Price + variant price” -> Price (make sure the variants contains parent price and then add its price to it)
    “Image1” - > image1, “Image2” - > image2 …etc
    “Description” ->Full Description
    “Brand” ->Brand
    If "variant name = camlızigon, Czigon Model, Kademeli Renk, Kiler Model, Kum Saati, Masa Model, MODEL-RENK, Puf Model, RENK, Smart Model, Tabure Model" -> Color
    If "variant name = B Ölçü, Başlık, Ölçüler" -> Size
    If "variant name = EBAT" -> Type
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "BRAND","FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","PRODUCT CODE","QUANTITY","COLOR","SIZE","TYPE", "DISCOUNTED PRICE"];
        $this->fieldNames = ["Product_code", "Name", "category", "Price","Brand", "Description","Image1","Image2","Image3","Image4","Image5","Product_code","Stock"];
        $this->varianceFields = ["barcode", "quantity","spec[@name='camlızigon' or @name='Czigon Model' or @name='Kademeli Renk' or @name='Kiler Model' or @name='Kum Saati' or @name='Masa Model' or @name='MODEL-RENK' or @name='Puf Model' or @name='RENK' or @name='Smart Model' or @name='Tabure Model']", "spec[@name='Ölçüler' or @name='B Ölçü' or @name='Başlık']","spec[@name='EBAT']"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "variants/variant";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://gozdemobilya.xmlbankasi.com/image/data/xml/gozdemobilya.xml');

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
                $c=count($this->fieldNames)-2;
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        $variantPrice=Parser::getXPathData($xpath, "price", $v);
                        $value["PRICE"]+=$variantPrice;
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        if ($value["PRODUCT CODE"]==""){
                            $value["PRODUCT CODE"]=Parser::getXPathData($xpath, "productCode", $v);
                        }
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i=0;$i<count($this->varianceFields)-2;$i++) {
                        $pvalue[$this->fieldHeaders[$c+$i+2]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}