<?php

class AlexanderGardiParser
{
    /*
   "Urun\ UrunKartiID" -> Group Code
    “Urun\ UrunAdi” -> Product Name
    “Secenek\ StokAdedi" -> Quantity
    “Urun\ KategoriTree”-> Category
    “Secenek\ StokKodu” -> product Code
    “Secenek\ AlisFiyati” -> Price
    “Secenek\ SatisFiyati” ->List Price
    “Secenek\ Resimler\ Resim” - > image1, “Secenek\ Resimler\ Resim” - > image2 …etc
    “Urun\ Aciklama” ->Full Description
    " Urun\ Marka" -> Brand
    “Secenek\ EkSecenekOzellik\ Ozellik Tanim="Beden" Deger=" -> Size
    “Secenek\ EkSecenekOzellik\ Ozellik Tanim="Renk" Deger=" -> Color
    “Urun\ UrunUrl”-> Product Link
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "FULL DESCRIPTION", "BRAND", "PRODUCT LINK", "PRODUCT CODE","PRICE", "LIST PRICE","SIZE","COLOR", "QUANTITY", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5",  "DISCOUNTED PRICE"];
        $this->fieldNames = ["UrunKartiID", "UrunAdi", "KategoriTree", "Aciklama", "Marka", "UrunUrl"];
        $this->varianceFields = ["StokKodu","AlisFiyati","SatisFiyati","EkSecenekOzellik/Ozellik[@Tanim='Beden']","EkSecenekOzellik/Ozellik[@Tanim='Renk']", "StokAdedi"];

        $this->productRoot = "//Urunler/Urun";
        $this->varianceRoot = "UrunSecenek/Secenek";
        $this->discount = (100 - $discount) / 100;
    }

    function parse()
    {

        $response = Parser::curlRequest('http://www.alexandergardi.com/TicimaxXml/F8E374D37546445F8EB7E94D5B29AD69/');

        $doc = new DOMDocument();
        $doc->loadXML(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $response));
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i => $fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }

            if ($pvalue["CATEGORY"]==""){
                $pvalue["CATEGORY"]=Parser::getFieldData($product,"Kategori");
            }

            if ($pvalue["GROUP CODE"] != "") {
                $variants = $xpath->query($this->varianceRoot, $product);
                $c = count($this->fieldNames);
                if (count($variants) > 0) {
                    foreach ($variants as $v) {
                        $value = array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i => $query) {
                            $value[$this->fieldHeaders[$c + $i]] = Parser::getXPathData($xpath, $query, $v);
                        }
                        $images = Parser::getXPathDataMulti($xpath, "Resimler/Resim", $v);

                        if (empty($images)){
                            $images=Parser::getXPathDataMulti($xpath, "Resimler/Resim", $product);
                        }
                        $value = Parser::setImages($value, $images, 5);
                        $value["PRICE"]=str_replace(",",".",$value["PRICE"]);
                        $value["LIST PRICE"]=str_replace(",",".",$value["LIST PRICE"]);
                        $value["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                        if ($value["SIZE"]!="" || $value["COLOR"]!="") {
                            fputcsv($file, $value);
                        }
                    }
                } else {
                    for ($i = 0; $i < count($this->varianceFields); $i++) {
                        $pvalue[$this->fieldHeaders[$c + $i]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"] = $value["PRICE"] * $this->discount;
                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}