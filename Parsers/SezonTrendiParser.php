<?php

class SezonTrendiParser
{
    /*
     Urun UrunKartiID" -> Group Code (if the product has no variants don't use group code)
    “Urun UrunAdi” -> Product Name
    “Secenek StokAdedi” -> Quantity
    “KategoriTree” -> Category
    If “Secenek StokKodu” is blank then “Urun UrunKartiID” -> product Code
    “Secenek AlisFiyati” -> Price (replace "," with ".")
    “Secenek AlisFiyati”” ->List Price (replace "," with ".")
    "Aciklama" -> Full Description
    "UrunUrl" -> Product Link
    “Resim” - > image1, “Resim” - > image2 …etc
    “Marka” ->Brand
    "Secenek Ozellik Tanim="Renk" -> Color
    "Secenek Ozellik Tanim="Beden" -> Size
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "BRAND","CATEGORY", "FULL DESCRIPTION","URL","PRODUCT CODE", "COLOR","SIZE", "PRICE","LIST PRICE","QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5","DISCOUNTED PRICE"];
        $this->fieldNames = ["UrunKartiID", "UrunAdi", "Marka","KategoriTree", "Aciklama","UrunUrl"];
        $this->varianceFields = ["StokKodu","EkSecenekOzellik/Ozellik[@Tanim='Renk']", "EkSecenekOzellik/Ozellik[@Tanim='Beden']","AlisFiyati","AlisFiyati", "StokAdedi"];

        $this->productRoot = "//Urunler/Urun";
        $this->varianceRoot = "UrunSecenek/Secenek";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('https://www.sezontrendi.com/TicimaxXml/28474C9A6439412DA0A0D9CF5C54FF8A/');

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

            $images = Parser::getXPathDataMulti($xpath, "Resimler/Resim", $product);


                $variants=$xpath->query($this->varianceRoot, $product);
                $c=count($this->fieldNames);
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        $value=Parser::setImages($value,$images,5);
                        $value["PRICE"]=str_replace(",",".",$value["PRICE"]);
                        $value["LIST PRICE"]=str_replace(",",".",$value["LIST PRICE"]);
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i = 0; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue["PRODUCT CODE"]=$pvalue["GROUP CODE"];
                    $pvalue["GROUP CODE"]="";
                    $pvalue=Parser::setImages($pvalue,$images,5);
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }

        fclose($file);
    }
}