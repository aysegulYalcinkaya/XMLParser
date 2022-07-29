<?php


class LisinyaParser
{

    /*
     If “Varyantlar/Varyant/VaryantCode” is empty then “Kod" -> product Code
    “Kod” -> Group Code
    “Baslik” -> Product Name
    “Kategori” -> Category
    “Marka” -> Brand
    “IndirimliFiyat” -> Price
    “Fiyat” -> List Price
    “Stok” -> Quantity
    “Aciklama” -> Full Description
    “Resim1” Image1 , “Resim2” Image2 … etc
    “Value” -> size
    “ColorName” -> Color
     */
    private $filename;
    private $fieldNames;
    private $fieldHeaders;
    private $productRoot;
    private $varianceRoot;
    private $varianceFields;
    private $discount;

    public function __construct($filename,$discount)
    {
        $this->filename = $filename;
        $this->fieldHeaders = ["PRODUCT CODE", "GROUP CODE", "PRODUCT NAME", "CATEGORY", "BRAND", "STOCK", "STATUS","PRICE", "LIST PRICE", "DESCRIPTION", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "SIZE", "COLOR","DISCOUNTED PRICE"];
        $this->fieldNames = ["Kod", "Kod", "Baslik", "Kategori", "Marka", "Stok", "Durum", "IndirimliFiyat","Fiyat",  "Aciklama", "Resim1", "Resim2", "Resim3", "Resim4", "Resim5"];
        $this->varianceFields = ["VaryantCode", "ColorName", "Value", "Stok"];

        $this->varianceRoot = "Varyantlar/Varyant";
        $this->productRoot = "//Urunler/Urun";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.lisinya.com/xmlexport/export.php?id=2');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);

        foreach ($xpath->query($this->productRoot) as $product) {

            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
            }
            $variances=$xpath->query($this->varianceRoot, $product);
            $pvalue["PRICE"]=str_replace(",","",$pvalue["PRICE"]);
            $pvalue["LIST PRICE"]=str_replace(",","",$pvalue["LIST PRICE"]);
            if (count($variances)>0) {
                foreach ($variances as $v) {
                    $value = array();
                    foreach ($this->varianceFields as $i=>$query) {
                        $value[] = Parser::getXPathData($xpath, $query, $v);
                    }
                    if ($value[0] != "") {
                        $pvalue["PRODUCT CODE"] = $value[0];
                    }

                    $pvalue["COLOR"] = $value[1];
                    $pvalue["SIZE"] = $value[2];
                    $pvalue["STOCK"] = $value[3];
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
            else{
                $pvalue["COLOR"] = "";
                $pvalue["SIZE"] = "";
                $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                fputcsv($file, $pvalue);
            }

        }

        fclose($file);
    }

}