<?php

class SilverSunKidsParser
{
    /*
   "Urun/Kod" -> Group Code
    “Urun/Baslik” -> Product Name
    “Miktar" -> Quantity
    “KategoriTree” -> Category
    “Barkod” -> product Code
    “Indirimli_Fiyati” -> Price
    “Fiyat” ->List Price
    “Resim” - > image1, “Resim” - > image2 …etc
    “Aciklama” ->Full Description
    Please make this "Silver Sun Kids" -> Brand
    If “isim3" = "Beden" -> Size
    If “isim3" = "Renk" -> Color
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
        $this->fieldHeaders = ["GROUP CODE", "PRODUCT NAME", "CATEGORY", "PRICE", "LIST PRICE", "FULL DESCRIPTION","BRAND","COLOR","SIZE", "PRODUCT CODE", "QUANTITY","DISCOUNTED PRICE", "IMAGE 1", "IMAGE 2", "IMAGE 3", "IMAGE 4", "IMAGE 5"];
        $this->fieldNames = ["Kod", "Baslik", "KategoriTree", "Indirimli_Fiyati", "Fiyat", "Aciklama"];
        $this->varianceFields = ["Ozellik[@isim='Renk']", "Ozellik[@isim='Beden']","Barkod", "Miktar"];

        $this->productRoot = "//Root/Urunler/Urun";
        $this->varianceRoot = "Stoklar/Stok";
        $this->discount=(100-$discount)/100;
    }

    function parse()
    {

        $response = Parser::curlRequest('www.silversunkids.com/XMLExport/FC637019DAB64AEEA432F6CACBDF04A6');

        $doc = new DOMDocument();
        $doc->loadXML(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $response));
        $xpath = new DOMXPath($doc);

        $file = fopen($this->filename, "w");
        fputcsv($file, $this->fieldHeaders);
        foreach ($xpath->query($this->productRoot) as $product) {
            $pvalue = array();
            foreach ($this->fieldNames as $i=>$fieldName) {
                $pvalue[$this->fieldHeaders[$i]] = Parser::getFieldData($product, $fieldName);
            }
            $pvalue["BRAND"]="Silver Sun Kids";
            if ($pvalue["GROUP CODE"] != "") {
                $images=Parser::getXPathDataMulti($xpath,"Resimler/Resim",$product);
                $variants=$xpath->query($this->varianceRoot, $product);

                $c=count($this->fieldNames)+1;

                if (count($variants)>0) {
                    foreach ($variants as $v) {
                        $value=array();
                        $value = $pvalue;
                        foreach ($this->varianceFields as $i=>$query) {
                            $value[$this->fieldHeaders[$c+$i]] = Parser::getXPathData($xpath, $query, $v);
                        }

                        $value["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                        $value=Parser::setImages($value,$images,5);
                        if ($value["PRODUCT CODE"]=="")
                            $value["PRODUCT CODE"]=$value["GROUP CODE"];

                        fputcsv($file, $value);
                    }
                }
                else{
                    for ($i=0;$i<count($this->varianceFields);$i++) {
                        $pvalue[$this->fieldHeaders[$c+$i]] = "";
                    }
                    $pvalue["DISCOUNTED PRICE"]=$value["PRICE"]*$this->discount;
                    $pvalue["PRODUCT CODE"]=$pvalue["GROUP CODE"];

                    fputcsv($file, $pvalue);
                }
            }
        }
        fclose($file);
    }
}