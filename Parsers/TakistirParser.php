<?php

class TakistirParser
{
    /* SIMILAR TO Ozpa
   "UrunKartiID" -> Group Code
    “UrunAdi” -> Product Name
    “UrunSecenek/Secenek/StokAdedi” -> Quantity
    “KategoriTree” -> Category
    “UrunSecenek/Secenek/StokKodu” -> product Code
    “UrunSecenek/Secenek/SatisFiyati” -> Price
    “Resim” - > image1, “Resim” - > image2 …etc
    “Aciklama” ->Full Description
    "Marka" -> Brand
    "UrunUrl" -> Product Link
    If "UrunSecenek/Secenek/EkSecenekOzellik/Ozellik/_Tanim"="Parmak Ölçüsü" -> Size
    If"UrunSecenek/Secenek/EkSecenekOzellik/Ozellik/_Tanim"="Renk" or "Saç Rengi" -> Color
    "UrunSecenek/Secenek/EkSecenekOzellik/Ozellik/_Tanim"="Burç", "Harf Seçiniz", "Kolye", "Kolye Harfi", or "Küpe Harfi" -> Shape
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "BRAND","CATEGORY", "FULL DESCRIPTION","URL","PRODUCT CODE", "COLOR","SIZE","SHAPE","PRICE","QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8", "IMAGE 9","DISCOUNTED PRICE"];
        $this->fieldNames = ["UrunKartiID", "UrunAdi", "Marka","KategoriTree", "Aciklama","UrunUrl"];
        $this->varianceFields = ["StokKodu","EkSecenekOzellik/Ozellik[@Tanim='Renk' or @Tanim='Saç Rengi']", "EkSecenekOzellik/Ozellik[@Tanim='Parmak Ölçüsü']","EkSecenekOzellik/Ozellik[@Tanim='Burç' or @Tanim='Harf Seçiniz' or @Tanim='Kolye' or @Tanim='Kolye Harfi' or @Tanim='Küpe Harfi']","SatisFiyati", "StokAdedi"];

        $this->productRoot = "//Urunler/Urun";
        $this->varianceRoot = "UrunSecenek/Secenek";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://www.takistir.com.tr/TicimaxXmlV2/20EE8E9BBD4A4A1891376F3E066D5E81/');

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

            if ($pvalue["GROUP CODE"] != "") {
                $variants=$xpath->query($this->varianceRoot, $product);
                $c=count($this->fieldNames);
                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                       $value=Parser::setImages($value,$images,9);
                        $value["PRICE"]=str_replace(",",".",$value["PRICE"]);
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i = 0; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue=Parser::setImages($pvalue,$images,9);
                    $pvalue["PRICE"]=str_replace(",",".",$pvalue["PRICE"]);
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}