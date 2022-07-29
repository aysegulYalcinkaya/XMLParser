<?php

class MuzikAletleriParser
{
    /*
     “urunbarkodu” -> Product Code
    “merchantLongName” -> Product Name
    “merchantStock” -> Quantity
    “merchantCategoryName” -> Category
    "merchantBrandName" -> Brand
    “merchantFirstPrice” -> Price
    "enuserprice" ->List Price
    “BigPicture” - > image1, “BigPicture2” - > image2 …etc
    “Description” ->Full Description
    */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "PRODUCT NAME", "CATEGORY", "BRAND","QUANTITY", "PRICE","LIST PRICE", "FULL DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "COLOR", "SIZE","DISCOUNTED PRICE"];
        $this->fieldNames = ["urunbarkodu", "merchantLongName", "merchantCategoryName", "merchantBrandName","merchantStock", "merchantFirstPrice", "enuserprice","Description", "BigPicture", "BigPicture2", "BigPicture3", "BigPicture4", "BigPicture5"];

        $this->productRoot = "//MerchantItems/MerchantItem";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.muzikaletleri.com.tr/outputxml/');

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
            $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
            fputcsv($file, $pvalue);
        }

        fclose($file);
    }
}