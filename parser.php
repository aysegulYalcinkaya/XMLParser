<html>
<head>
    <title>XML Parser</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body style="margin: 50px">
<h1 style="text-align: left">XML Parser Result</h1>

<?php
set_time_limit(0);
include_once "Parsers/Parser.php";

include_once "Parsers/AkayevParser.php";
include_once "Parsers/AlexanderGardiParser.php";
include_once "Parsers/AllevParser.php";
include_once "Parsers/AyakkabiFrekansiParser.php";
include_once "Parsers/BasicToptanParser.php";
include_once "Parsers/BayichekichParser.php";
include_once "Parsers/BigdartParser.php";
include_once "Parsers/BuyukbedenizParser.php";
include_once "Parsers/CicibebeAyakkabiParser.php";
include_once "Parsers/CosstaParser.php";
include_once "Parsers/DegerindenAlParser.php";
include_once "Parsers/EBijuteriParser.php";
include_once "Parsers/EpocaleParser.php";
include_once "Parsers/EsarpGalasiParser.php";
include_once "Parsers/EWSParser.php";
include_once "Parsers/FularcimParser.php";
include_once "Parsers/GozdeMobilyaParser.php";
include_once "Parsers/GuardLeatherParser.php";
include_once "Parsers/GumushParser.php";
include_once "Parsers/HasemaParser.php";
include_once "Parsers/KarpefingoParser.php";
include_once "Parsers/LactoneParser.php";
include_once "Parsers/LisinyaParser.php";
include_once "Parsers/MaskButikParser.php";
include_once "Parsers/MidyatGumusDunyasiParser.php";
include_once "Parsers/MobettoMobilyaParser.php";
include_once "Parsers/ModaCizgiParser.php";
include_once "Parsers/ModaPinhanParser.php";
include_once "Parsers/ModaVitriniParser.php";
include_once "Parsers/MovimentParser.php";
include_once "Parsers/MuzikAletleriParser.php";
include_once "Parsers/NevresimDunyasiParser.php";
include_once "Parsers/OzpaParser.php";
include_once "Parsers/ParlaParser.php";
include_once "Parsers/PonibaParser.php";
include_once "Parsers/RiccotarzParser.php";
include_once "Parsers/RapellinParser.php";
include_once "Parsers/SamursakaParser.php";
include_once "Parsers/SezonTrendiParser.php";
include_once "Parsers/SilverSunKidsParser.php";
include_once "Parsers/TakistirParser.php";
include_once "Parsers/TarzimSuperParser.php";
include_once "Parsers/TatlisBebeParser.php";
include_once "Parsers/TonnyMoodParser.php";
include_once "Parsers/TuremMobilyaParser.php";
include_once "Parsers/Twenty3Parser.php";
include_once "Parsers/VprModaParser.php";
include_once "Parsers/WagoonAyakkabiParser.php";
include_once "Parsers/ZavansaParser.php";


if (isset($_POST["supplier"]) && $_POST["supplier"]!=""){
    $supplier=$_POST["supplier"];
    $discount=isset($_POST["discount"])?$_POST["discount"]:0;

    $filename=$supplier.".csv";
    switch ($supplier){
        case "akayev":
            $parser=new AkayevParser($filename,$discount);
           break;
        case "alexanderGardi":
            $parser=new AlexanderGardiParser($filename,$discount);
            break;
        case "allev":
            $parser=new AllevParser($filename,$discount);
            break;
        case "ayakkabiFrekansi":
            $parser=new AyakkabiFrekansiParser($filename,$discount);
            break;
        case "basicToptan":
            $parser=new BasicToptanParser($filename,$discount);
            break;
        case "bayichekich":
            $parser=new BayichekichParser($filename,$discount);
            break;
        case "bigdart":
            $parser=new BigdartParser($filename,$discount);
            break;
        case "buyukbedeniz":
            $parser=new BuyukbedenizParser($filename,$discount);
            break;
        case "cicibebeAyakkabi":
            $parser=new CicibebeAyakkabiParser($filename,$discount);
            break;
        case "cossta":
            $parser=new CosstaParser($filename,$discount);
            break;
        case "degerindenAl":
            $parser=new DegerindenAlParser($filename,$discount);
            break;
        case "ebijuteri":
            $parser=new EBijuteriParser($filename,$discount);
            break;
        case "epocale":
            $parser=new EpocaleParser($filename,$discount);
            break;
        case "esarpGalasi":
            $parser=new EsarpGalasiParser($filename,$discount);
            break;
        case "ews":
            $parser=new EWSParser($filename,$discount);
            break;
        case "fularcim":
            $parser=new FularcimParser($filename,$discount);
            break;
        case "gozdeMobilya":
            $parser=new GozdeMobilyaParser($filename,$discount);
            break;
        case "guardLeather":
            $parser=new GuardLeatherParser($filename,$discount);
            break;
        case "gumush":
            $parser=new GumushParser($filename,$discount);
            break;
        case "hasema":
            $parser=new HasemaParser($filename,$discount);
            break;
        case "karpefingo":
            $parser=new KarpefingoParser($filename,$discount);
            break;
        case "lactone":
            $parser=new LactoneParser($filename,$discount);
            break;
        case "lisinya":
            $parser=new LisinyaParser($filename,$discount);
            break;
        case "maskButik":
            $parser=new MaskButikParser($filename,$discount);
            break;
        case "midyatGumusDunyasi":
            $parser=new MidyatGumusDunyasiParser($filename,$discount);
            break;
        case "mobettoMobilya":
            $parser=new MobettoMobilyaParser($filename,$discount);
            break;
        case "modaCizgi":
            $parser=new ModaCizgiParser($filename,$discount);
            break;
        case "modaPinhan":
            $parser=new ModaPinhanParser($filename,$discount);
            break;
        case "modaVitrini":
            $parser=new ModaVitriniParser($filename,$discount);
            break;
        case "moviment":
            $parser=new MovimentParser($filename,$discount);
            break;
        case "muzikAletleri":
            $parser=new MuzikAletleriParser($filename,$discount);
            break;
        case "nevresimDunyasi":
            $parser=new NevresimDunyasiParser($filename,$discount);
            break;
        case "ozpa":
            $parser=new OzpaParser($filename,$discount);
            break;
        case "parla":
            $parser=new ParlaParser($filename,$discount);
            break;
        case "poniba":
            $parser=new PonibaParser($filename,$discount);
            break;
        case "rapellin":
            $parser=new RapellinParser($filename,$discount);
            break;
        case "riccotarz":
            $parser=new RiccotarzParser($filename,$discount);
            break;
        case "samursaka":
            $parser=new SamursakaParser($filename,$discount);
            break;
        case "sezonTrendi":
            $parser=new SezonTrendiParser($filename,$discount);
            break;
        case "silverSunKids":
            $parser=new SilverSunKidsParser($filename,$discount);
            break;
        case "takistir":
            $parser=new TakistirParser($filename,$discount);
            break;
        case "tarzimSuper":
            $parser=new TarzimSuperParser($filename,$discount);
            break;
        case "tatlisBebe":
            $parser=new TatlisBebeParser($filename,$discount);
            break;
        case "tonnyMood":
            $parser=new TonnyMoodParser($filename,$discount);
            break;
        case "turemMobilya":
            $parser=new TuremMobilyaParser($filename,$discount);
            break;
        case "twenty3":
            $parser=new Twenty3Parser($filename,$discount);
            break;
        case "vprModa":
            $parser=new VprModaParser($filename,$discount);
            break;
        case "wagoonAyakkabi":
            $parser=new WagoonAyakkabiParser($filename,$discount);
            break;
        case "zavansa":
            $parser=new ZavansaParser($filename,$discount);
            break;
        default:
            $parser=null;
    }
    if ($parser){
        $parser->parse();
        echo "<div style='padding-bottom: 20px'><a href='".$filename."'>Download csv file</a></div>";
        echo "<div><a href='index.php'>Back to XMLParser</a></div>";
    }
}
else{
    echo "<script>
            alert('Select Supplier');
            window.location.href='index.php';
        </script>";
}
?>
</body>
</html>

