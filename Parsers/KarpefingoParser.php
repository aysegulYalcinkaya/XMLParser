<?php

class KarpefingoParser
{
    /* SIMILAR TO Ozpa
    “Urun UrunKartiID"-> Group Code
    “Urun UrunAdi” -> Product Name
    “Secenek StokAdedi” -> Quantity
    “Urun KategoriTree” -> Category
    “Secenek Barkod” -> product Code
    “Secenek IndirimliFiyat” -> Price (replace "," with ".")
    “Secenek SatisFiyati” ->List Price (replace "," with ".")
    “Resim” - > image1, “Resim” - > image2 …etc
    "UrunUrl" -> Link
    “Aciklama” ->Full Description
    “Marka” ->Brand
    "Secenek Ozellik Tanim="Beden" -> Size
    "Secenek StokKodu -> Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "BRAND","CATEGORY", "FULL DESCRIPTION","URL","COLOR","PRODUCT CODE","SIZE",  "PRICE","LIST PRICE","QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5", "IMAGE 6", "IMAGE 7", "IMAGE 8", "IMAGE 9","DISCOUNTED PRICE"];
        $this->fieldNames = ["UrunKartiID", "UrunAdi", "Marka","KategoriTree", "Aciklama","UrunUrl","TeknikDetaylar/TeknikDetay/DegerTanim"];
        $this->varianceFields = ["Barkod", "EkSecenekOzellik/Ozellik[@Tanim='Beden']","IndirimliFiyat","SatisFiyati", "StokAdedi"];

        $this->productRoot = "//Urunler/Urun";
        $this->varianceRoot = "UrunSecenek/Secenek";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('www.karpefingo.com/TicimaxXmlV2/AC60934D515946ED84BBBDD1904C0024/');

        $doc = new DOMDocument();
        $doc->loadXML($response);
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getXPathData($xpath, $fieldName,$product);
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
                        $value["LIST PRICE"]=str_replace(",",".",$value["LIST PRICE"]);
                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i = 0; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue=Parser::setImages($pvalue,$images,9);
                    $pvalue["DISCOUNTED PRICE"]=$pvalue["PRICE"]*$this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}