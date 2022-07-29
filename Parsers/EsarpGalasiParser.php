<?php

class EsarpGalasiParser
{

    /*
     “Varyant AnaStokKod -> Group Code
    “Baslik” -> Product Name
    If “Varyant StokAdet” is blank then “Urun StokAdet” -> Quantity
    “Kategori” -> Category
    If “Varyant StokKod” is blank then “Urun StokKod” -> product Code
    “Fiyat3” -> Price
    “Fiyat4” ->List Price
    “Resim” - > image1, “Resim” - > image2 …etc
    “Aciklama” ->Full Description
    “Marka” ->Brand
    "url" -> Product Link
    "Varyant Beden" -> Size
    "Varyant Renk" - Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "BRAND", "PRICE", "LIST PRICE", "DESCRIPTION","PRODUCT CODE", "STOCK", "SIZE", "COLOR","IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["Varrot/Varyant/AnaStokKod", "Baslik", "Kategori", "Marka", "Fiyat3","Fiyat4",  "Aciklama","StokKod","StokAdet","Beden","Renk"];
        $this->varianceFields = ["StokKod","StokAdet", "Beden", "Renk"];

        $this->varianceRoot = "Varrot/Varyant";
        $this->productRoot = "//Urunler/Urun";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $urls=['https://semercioglutoptan.com/veripaylasim.aspx?ID=37&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=38&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=39&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=40&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=41&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=42&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=43&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=44&kdv=dahil&yayin=1',
            'https://semercioglutoptan.com/veripaylasim.aspx?ID=45&kdv=dahil&yayin=1'];

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);

        foreach ($urls as $url) {
            $response = Parser::curlRequest($url);

            $doc = new DOMDocument();
            $doc->loadXML($response);
            $xpath = new DOMXPath($doc);


            foreach ($xpath->query($this->productRoot) as $product) {
                $pvalue = array();
                foreach ($this->fieldNames as $i => $fieldName) {
                    $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName, $product);
                }
                $variances = $xpath->query($this->varianceRoot, $product);

                if (count($variances) > 0) {
                    foreach ($variances as $v) {
                        $value = array();
                        foreach ($this->varianceFields as $i => $query) {
                            $value[] = Parser::getXPathData($xpath, $query, $v);
                        }
                        if ($value[0] != "") {
                            $pvalue["PRODUCT CODE"] = $value[0];
                        }

                        $pvalue["STOCK"] = $value[1];
                        $pvalue["SIZE"] = $value[2];
                        $pvalue["COLOR"] = $value[3];
                        $images = $xpath->query("Resimler/Resim", $v);
                        $pvalue = Parser::setImages($pvalue, $images, 5);
                        $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                        fputcsv($file, $pvalue);
                    }
                } else {
                    $images = $xpath->query("Resimler/Resim", $product);
                    $pvalue = Parser::setImages($pvalue, $images, 5);
                    $pvalue["DISCOUNTED PRICE"] = $pvalue["PRICE"] * $this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }

}