<?php

class TuremMobilyaParser
{
    /*
     “item stockCode-> Group Code (if the product has no variants don't use group code)
    “item label” -> Product Name
    If “variant vStockAmount” is blank then “item stockAmount” -> Quantity
    “item category” -> Category
    If “variant vStockCode” is blank then “item stockCode” -> product Code
    If “variant vRebate” is blank then “item rebate” -> Price
    If “variant vPrice1” is blank then “item price1” ->List Price
    "details" -> Full Description
    “picture1Path” - > image1, “picture2Path” - > image2 …etc
    “brand” ->Brand
    If "variant option variantName = Ölçü" -> Size
    If "variant option variantName =renk" -> Color
    If "variant option variantName =Seçenekler" -> Type
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY",  "FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "PRICE","LIST PRICE","PRODUCT CODE", "QUANTITY","SIZE","COLOR","TYPE","DISCOUNTED PRICE"];
        $this->fieldNames = ["stockCode", "label", "category", "details","picture1Path","picture2Path","picture3Path","picture4Path"];
        $this->varianceFields = ["vRebate","vPrice1","vStockCode", "vStockAmount","options/option[variantName='Ölçü' or variantName='ölçü']/variantValue","options/option[variantName='Renk' or variantName='renk']/variantValue","options/option[variantName='Seçenekler']/variantValue"];

        $this->productRoot = "//root/item";
        $this->varianceRoot = "variants/variant";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.turemmobilya.com/index.php?do=catalog/output&pCode=7036758048');

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



                $variants = $xpath->query($this->varianceRoot, $product);
                $c=count($this->fieldNames);
                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $value = array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                } else {
                    $pvalue["PRICE"]=Parser::getFieldData($product,"rebate");
                    $pvalue["LIST PRICE"]=Parser::getFieldData($product,"price1");
                    $pvalue["PRODUCT CODE"]=$pvalue["GROUP CODE"];
                    $pvalue["GROUP CODE"]="";
                    $pvalue["QUANTITY"]=Parser::getFieldData($product,"stockAmount");
                    $pvalue["SIZE"]="";
                    $pvalue["COLOR"]="";
                    $pvalue["TYPE"]="";
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }

        fclose($file);
    }
}
