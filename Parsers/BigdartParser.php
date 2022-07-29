<?php

class BigdartParser
{
    /*
     “Product_code” -> Group Code
    “Name” -> Product Name
    “Stock” -> Quantity
    “Category” -> Category
    “variant productCode” -> product Code
    “Price2kdvli” -> Price
    “Price1kdvli” ->List  Price
    “Image1” - > image1, “Image2” - > image2 …etc
    “Description” ->Full Description
    “variant spec name="Renk" ” ->Color

    “variant spec name="Beden" ” ->Size
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","COLOR","SIZE", "PRODUCT CODE", "QUANTITY","DISCOUNTED PRICE"];
        $this->fieldNames = ["Product_code", "Name", "category", "Price2kdvli", "Price1kdvli", "Description","Image1","Image2","Image3","Image4","Image5"];
        $this->varianceFields = ["spec[@name='Renk']", "spec[@name='Beden']","productCode", "quantity"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "variants/variant";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://bigdartr.xmlbankasi.com/image/data/xml/doujly.xml');

        $doc = new DOMDocument();
        $doc->loadXML(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $response));
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
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i=0;$i<count($this->varianceFields);$i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}