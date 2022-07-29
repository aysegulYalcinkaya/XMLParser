<?php

class Twenty3Parser
{
    /*
     “product sku" -> Group Code
    “product title” -> Product Name
    “variant variantStock” -> Quantity
    “product categoryPath” -> Category
    “variant variantSku” -> product Code
    “variant variantCostPrice” -> Price
    “variant variantSalePrice” -> List Price
    "product detail" -> Full Description
    "product brand"-> Brand
    “image src” - > image1, “image src” - > image2 …etc (Make sure that the image attributeLabel Renk = variant attribute label="Renk" to link the right image color with the correct color name of the item)
    If "variant attribute label="Renk" -> Color
    If "variant attribute label="Beden" -> Size
     */
    /*

    “salePrice” -> Price
    “Image” - > image1,  image2 …etc
    “variant attribute label="Renk" ” ->Color


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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME","BRAND", "CATEGORY", "FULL DESCRIPTION", "PRICE","LIST PRICE","SIZE", "COLOR", "PRODUCT CODE", "QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6","DISCOUNTED PRICE"];
        $this->fieldNames = ["sku", "title", "brand","categoryPath", "detail"];
        $this->varianceFields = ["variantCostPrice","variantSalePrice","attributes/attribute[2]/value", "attributes/attribute[1]/value", "variantSku", "variantStock"];

        $this->productRoot = "//products/product";
        $this->varianceRoot = "variants/variant";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://twenty3.com.tr/xml/products/merchant/68131');

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

            $images = Parser::getXPathDataMulti($xpath, "images/image", $product);

            if ($pvalue["GROUP CODE"] != "") {
                $variants = $xpath->query($this->varianceRoot, $product);
                $c=count($this->fieldNames);
                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $value = array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        $i=1;
                        if ($images) {
                            foreach ($images as $image) {
                                if (Parser::getXPathData($xpath, "attributeLabel", $image) == "Renk" && Parser::getXPathData($xpath, "attributeValue", $image) == $value["COLOR"]) {
                                    $value["IMAGE ".$i] = Parser::getXPathData($xpath, "src", $image);
                                    $i++;
                                }
                            }
                        }
                        for ($i=$i;$i<7;$i++)
                            $value["IMAGE ".$i]="";
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                } else {
                    for ($i = 0; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }

                    $i=0;
                    foreach ($images as $i=>$image) {
                        $pvalue["IMAGE ".($i+1)] = Parser::getXPathData($xpath, "src", $image);
                    }
                    for ($i=$i;$i<7;$i++)
                        $pvalue["IMAGE ".($i+1)]="";
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}
