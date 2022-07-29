<?php

class WagoonAyakkabiParser
{
    /* SAME AS AYAKKABI FREKANSI
     “Product Sku" -> Group Code
    “Product Name” -> Product Name
    “Combination StockQuantity” -> Quantity
    “Product Category Id="6" Path ” -> Category
    “Combination Sku” -> product Code
    “Product Price3” -> Price (replace "," with ".")
    “Product Price” -> List Price (replace "," with ".")
    "Product FullDescription" -> Full Description
    "Product Manufacturer Name"-> Brand
    "Product Url"-> Product Link
    “Picture Path” - > image1, “Picture Path” - > image2 …etc
    "Specification Name="Renk" -> Color
    "Attribute Name="Numara" -> Size
     */


    private $filename;
    private $fieldNames;
    private $fieldHeaders;

    private $variantNames;
    private $variantRoot;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE","LIST PRICE", "BRAND","PRODUCT LINK","FULL DESCRIPTION","COLOR","PRODUCT CODE","QUANTITY","SIZE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["@Sku", "@Name", "Categories/Category[@Id='6']/@Path", "@Price3", "@Price","Manufacturers/Manufacturer/@Name","@Url","@FullDescription","Specifications/Specification[@Name='Renk']/@Value"];
        $this->variantNames=["@Sku","@StockQuantity","Attributes/Attribute[@Name='Numara']/@Value"];

        $this->productRoot = "//Products/Product";
        $this->variantRoot="Combinations/Combination";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.wagoonayakkabi.com/FaprikaXml/2LMPZE/1/');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);

        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName,$product);
            }
            $pvalue["PRICE"]=str_replace(",",".",$pvalue["PRICE"]);
            $pvalue["LIST PRICE"]=str_replace(",",".",$pvalue["LIST PRICE"]);
            $images=Parser::getXPathDataMulti($xpath,"Pictures/Picture/@Path",$product);

            $variants=$xpath->query($this->variantRoot,$product);
            $c=count($this->fieldNames);
            if (count($variants)>0) {
                foreach ($variants as $v) {
                    $value = array();
                    $value = $pvalue;
                    foreach ($this->variantNames as $i => $variantName) {
                        $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $variantName, $v);
                    }
                   $value=Parser::setImages($value,$images,5);
                    $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    fputcsv($file, $value);
                }
            }
            else{
                foreach ($this->variantNames as $i => $variantName) {
                    $pvalue[$this->fieldHeaders[$c+$i]] = "";
                }
                $pvalue=Parser::setImages($value,$images,5);
                $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                fputcsv($file, $pvalue);
            }
        }
        fclose($file);
    }
}