<?php

class TatlisBebeParser
{
    /*
     " item \ stockCode " -> Group Code
    " item \ label " -> Product Name
    “variant \ vStockAmount " -> Quantity
    " item \ mainCategory" -> Category
    “variant \ vStockCode" -> product Code
    “variant \ vRebate" -> Price
    “variant \ vPrice1" -> List Price
    " item \ picture1Path" - > image1, " item \ picture2Path" - > image2 …etc
    " item \ details " ->Full Description
    " item \ brand" -> Brand
    If “variant\ variantName" = “Beden” -> Size
    If “variant\ variantName" = “Renk” -> Color
     */

    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $discount;

    public function __construct($filename, $discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "FULL DESCRIPTION", "BRAND","IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "PRICE", "LIST PRICE", "COLOR", "SIZE", "PRODUCT CODE", "QUANTITY", "DISCOUNTED PRICE"];
        $this->fieldNames = ["stockCode", "label", "mainCategory", "details", "brand","picture1Path","picture2Path","picture3Path","picture4Path", "rebate", "price1"];
        $this->varianceFields = ["vRebate", "vPrice1", "options/option/variantName[text()='Renk']/../variantValue","options/option/variantName[text()='Beden']/../variantValue", "vStockCode","vStockAmount"];

        $this->productRoot = "//root/item";
        $this->varianceRoot = "variants/variant";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://www.tatlisbebe.com/index.php?do=catalog/output&pCode=417676262');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }

            if ($pvalue["GROUP CODE"] != "") {

                $variants = $xpath->query($this->varianceRoot, $product);
                $c = count($this->fieldNames);

                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $value = array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i => $query) {
                            $value[$this->fieldHeaders[$c + $i-2]] = Parser::getXPathData($xpath, $query, $v);
                        }

                        $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                        fputcsv($file, $value);
                    }
                } else {

                    for ($i = 0; $i < count($this->varianceFields)-2; $i++) $pvalue[$this->fieldHeaders[$c + $i]] = "";

                    $pvalue["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}