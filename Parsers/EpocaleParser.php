<?php

class EpocaleParser
{

    /*
    " item \ stockCode" -> Group Code
    “item \ label” -> Product Name
    “variant \ vStockAmount " -> Quantity
    Join “item \ mainCategory” to “item \ category” with a space between them -> Category
    “variant \ vStockCode " -> product Code
    “variant \ vRebate " -> Price
    “variant \ vPrice1 " -> List Price
    “item \ picture1Path " - > image1, “item \ picture2Path " - > image2 …etc
    “item \ details” ->Full Description
    “item \ brand” -> Brand
    “variant \variantValue" -> Size
     */
    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $varianceFields;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "BRAND",  "DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "PRODUCT CODE","PRICE", "LIST PRICE", "STOCK", "SIZE", "DISCOUNTED PRICE"];
        $this->fieldNames = ["stockCode", "label", "mainCategory", "brand", "details","picture1Path","picture2Path","picture3Path","picture4Path","picture5Path"];
        $this->varianceFields = ["vStockCode", "vRebate", "vPrice1", "vStockAmount","options/option/variantValue"];

        $this->productRoot = "//root/item";
        $this->varianceRoot = "variants/variant";

        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $url = 'http://www.epocale.com.tr/index.php?do=catalog/output&pCode=3086672431';

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);


        $response = Parser::curlRequest($url);

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);


        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }

            $cat= Parser::getXPathData($xpath, "category", $product);
            $pvalue["CATEGORY"].=" ".$cat;
            $variances = $xpath->query($this->varianceRoot, $product);
            $c = count($this->fieldNames);

            if (count($variances) > 0) {
                foreach ($variances as $v) {
                    $value = $pvalue;
                    foreach ($this->varianceFields as $i => $query) {
                        $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);

                    }

                    $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;

                    fputcsv($file, $value);
                }
            } else {

                $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                fputcsv($file, $pvalue);
            }
        }

        fclose($file);
    }

}