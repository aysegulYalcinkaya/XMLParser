<?php

class VprModaParser
{
    /*
     * “Product GroupProductCode" -> Group Code
    “Product ProductName” -> Product Name
    “Variant VariantStock” -> Quantity
    “Product Category” -> Category
    “Variant VariantCode” -> product Code
    “Variant VariantSale” -> Price (replace "," with ".")
    "Product Features" -> Full Description
    “Image” - > image1, “Image” - > image2 …etc
    "Product Color" -> Color
    "Variant VariantName" -> Size
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "FULL DESCRIPTION", "COLOR","PRICE", "SIZE", "PRODUCT CODE", "QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6","DISCOUNTED PRICE"];
        $this->fieldNames = ["GroupProductCode", "ProductName", "Category", "Features", "Color"];
        $this->varianceFields = ["VariantSale","VariantName", "VariantCode", "VariantStock"];

        $this->productRoot = "//Products/Product";
        $this->varianceRoot = "Variants/Variant";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.vprmoda.com/Xml/Standart/?5066_5500128');

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

            $images = Parser::getXPathDataMulti($xpath, "Images/Image", $product);


                $variants = $xpath->query($this->varianceRoot, $product);
                $c=count($this->fieldNames);
                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $value = array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        $value=Parser::setImages($value,$images,6);
                        $value["PRICE"]=str_replace(",",".",$value["PRICE"]);
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                } else {
                    for ($i = 0; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }

                   $pvalue=Parser::setImages($pvalue,$images,6);
                    $pvalue["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }

        fclose($file);
    }
}
