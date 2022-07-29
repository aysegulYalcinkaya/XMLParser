<?php

class EWSParser
{
    /*
   "Brand" ->Brand
    “Label” -> Product Name
    “stockAmount” -> Quantity
    “CategoryTree” -> Category
    “Barcode” -> product Code
    “price1” -> Price
    “sitePrice” ->List Price
    “Picture1Path” - > image1, “Picture2Path” - > image2 …etc
    “Details” ->Full Description
    “Color” ->Color
     */
    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["Brand", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION", "COLOR", "PRODUCT CODE", "STOCK", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["brand", "label", "categoryTree", "price1", "sitePrice", "details", "color", "barcode", "stockAmount","picture1Path","picture2Path","picture3Path","picture4Path","picture5Path"];
        $this->productRoot = "//root/item";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://www.ewsyonetim.com/salyangoz-export?company_id=1');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath,$fieldName,$product);
            }
            $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
            fputcsv($file, $pvalue);
        }

        fclose($file);
    }

}