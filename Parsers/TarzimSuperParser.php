<?php

class TarzimSuperParser
{
    /*
     “Product Product_code" -> Group Code
    “Product Name” -> Product Name
    “variant quantity” -> Quantity
    “category” -> Category
    “variant productCode” -> product Code
    “fiyat” -> Price
    “Price” ->List Price
    "Description" -> Full Description
    “Image1” - > image1, “Image2” - > image2 …etc
    “Brand" ->Brand
    "variant spec name="RENK" -> Color
    "variant spec name="BEDEN" -> Size
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $dicount;

    public function __construct($filename,$dicount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY","BRAND", "PRICE", "LIST PRICE", "FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","COLOR","SIZE", "PRODUCT CODE", "QUANTITY","DISCOUNTED PRICE"];
        $this->fieldNames = ["Product_code", "Name", "category", "Brand", "fiyat", "Price","Description","Image1","Image2","Image3","Image4","Image5"];
        $this->varianceFields = ["spec[@name='RENK']", "spec[@name='BEDEN']","productCode", "quantity"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "variants/variant";
        $this->dicount=(100-$dicount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://tarzimsuper2020.xmlbankasi.com/image/data/xml/sanalpazar.xml');

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
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->dicount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    foreach ($this->varianceFields as $i=>$query) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->dicount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}