<?php
function getList($url)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_HTTPHEADER => array('accept:  text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9', 'accept-encoding:  gzip, deflate, br', 'accept-language:  en-US,en;q=0.9', 'cache-control:  max-age=0', 'cookie:  ASP.NET_SessionId=4pui1nxvghfjentokdnyi2wi; visitorId=710d269a-cb7d-4a9e-a03f-36f89d43bf73; guestSessionId=7178ca6c-f378-4f9a-a207-f2dfa86d25b6; _gcl_au=1.1.774996500.1642493263; undefined=-1; VLCV1OK=1; OfferMiner_ID=CKXGODCXVVOKPSUM20220118110743; ins-customer-id=; _gid=GA1.2.769437199.1642493264; ins-CVcustomer-id=null; policyCookie=true; _gaexp=GAX1.2.DpPba6aRRQCuVDAvl9RPnw.19084.1; _hjSessionUser_647033=eyJpZCI6IjZkMzc2ODE1LWJiZWQtNTIwNS05ZGY5LTM5NDI5MWQ0M2NmZCIsImNyZWF0ZWQiOjE2NDI0OTMyNjQ1MDEsImV4aXN0aW5nIjp0cnVlfQ==; _fbp=fb.1.1642493292629.1067612942; _hjDonePolls=681414; _ExVID=5107032; ins-CV_UserId=5107032; serial-visit-counter=%7B%22%2Fkadin%22%3A13%2C%22%2Ferkek%22%3A2%7D; _abck=C7F69EBCAF1605E3E9FEEEB85E0A6846~0~YAAQTqwVAn/XOrV9AQAA6YKkbgfqVmuk+Fne1TCcytt31UkXlvXQCNY4ynauIpz1qshdYMfZXz+lxTaD3CWO72yv/gA+UMxStEzmysX4aWQzloWbp7gt4TOv8XVKcSlJeAE84/5OFC6fH61AHlz7c0zXLZ2Gy/BXSe1izX4IprUmqqfPH6eSqtbGiWuHJchVSB7w90UOPPwILJ3z7eP9ElU6CXycl8TA95l0G0XS3Sn523xrlvDBnU73XDyInjPlXVJf787tq7vEqe1DBSzcg4C2NkyOI8XdGMSS3VLg9bWFu7iG5md4peRQNIs0JmhjBgDtfQLyaTGoxDKv+V0VICcpLuZmzJ5PgcpbDLWlIeFtNDV9YGUD2Fmn82DU2W1vXgKwO23ZKlT8NV3Er+Uzna9T+9hHhzI1dULE~-1~-1~-1; insLastVisitedCategory=Yeni%20Gelenler; previousPageLink=https://www.lcwaikiki.com/tr-TR/TR/lcw-home/banyo/banyo_aksesuarlari-260; insLastVisitedProd=%7B%22id%22%3A%22W1CG85Z8-PCJ%22%2C%22name%22%3A%22Lastikli%2520Kad%25C4%25B1n%2520Sa%25C3%25A7%2520Havlusu%22%2C%22price%22%3A24.99%2C%22originalPrice%22%3A24.99%2C%22img%22%3A%22https%3A%2F%2Fimg-lcwaikiki.mncdn.com%2Fmnresize%2F320%2F-%2Fpim%2Fproductimages%2F20202%2F5344686%2Fv1%2Fl_20202-w1cg85z8-pcj_a.jpg%22%2C%22url%22%3A%22https%3A%2F%2Fwww.lcwaikiki.com%2Ftr-TR%2FTR%2Furun%2FLCW-HOME%2Fbanyo%2FSac-Havlusu%2F5344686%2F2265242%22%2C%22quantity%22%3A1%2C%22cats%22%3A%5B%22LCW%20Home%22%2C%22Banyo%22%2C%22Banyo%20Aksesuarlar%C4%B1%22%5D%2C%22time%22%3A1642534593%2C%22variants%22%3A%5B%22W1CG85Z8-CTK%22%2C%22W1CG85Z8-RR0%22%2C%22W1CG85Z8-T89%22%2C%22W1CG85Z8-FTG%22%2C%22W1CG85Z8-EEW%22%2C%22W1CG85Z8-FBY%22%5D%2C%22groupcode%22%3A%22W1CG85Z8%22%2C%22product_attributes%22%3A%7B%22addtocartproductids_v2%22%3A%22%5B%7B%5C%22id%5C%22%3A12197610%2C%5C%22sT%5C%22%3A%5C%22Standart%5C%22%2C%5C%22s%5C%22%3A1%7D%5D%22%7D%7D; _ga=GA1.1.206610361.1642493263; cto_bundle=RwrvJF9CQnQ1c3Iza2NLUzJxdExOemNWVW9PcGJ0S3hOVlVaNGs2TVVlcGhSTDRTUXhEamQ4ZmZMSDh1RUhYVmFqVyUyRmJyeHZwY25wc2IwdmhaUWVUYnE1V1JzRzclMkZLZG9lY1hPbzNYYVFuJTJCaWFBM2NxSXZ5cGZJeURxRnlJOHI3OW5oam84N29HSkpZY0ZERFNCalFNcVo4VUElM0QlM0Q; CustomRequestVerificationToken=K4l-YVYHn8Ua8jsnXvcYg7QV5RVMEAHmys745iSSZMSh5kXIqtcbNzwjbjA9JrdVuGKLNQ2; search_superCampaign_Order=0; ADRUM=s=1642535757913&r=https%3A%2F%2Fwww.lcwaikiki.com%2Ftr-TR%2FTR%2Fsayfa-bulunamadi%3F0; _ga_C24SJ6JN7Y=GS1.1.1642534404.2.1.1642535757.56; _abck=C7F69EBCAF1605E3E9FEEEB85E0A6846~-1~YAAQfLSvw4bDjeN9AQAAu0dzcQfiDLMUChJKPumrNNQTBrF8oZf/GybtLSTkhl4Zswhbs8Wwp5XVZrt0HNralnQ1wuA0IrgrrwFGBB0oyfkpluS2YJ2Szad0Vgn7niBSdTUEIpGH8flQjO4nXg+jNNAMBhapxohNljCCrAA320KowL82srTTy5GvDk3fgqn7cTTYSs11VeS1fm6xhnZL1nG81SvkNE3f72DKGM3sLIoaE+/IhzqUsyW+L+EcQhW98bR16lC9hVX0+p6kD9vFCfPIb/VXACJ41r887IOjGijdoGiJFavjFoU5H1DYHM4lSz4Xsnx8ae7tFDZEPRBX8+CU4V+oxs4u3SYlgCMUp8WKryVt6uEEVLkYckJwt/gXpXSu/UGVswFw67BMInNzrY5WXQR4HE5eesw5~0~-1~-1; ak_bmsc=FA7C37B3862804A4E65C2D6BA4D23393~000000000000000000000000000000~YAAQfLSvw4fDjeN9AQAAu0dzcQ5O3BxeiUWIv4izUxOHPxy1kJ7y1im3XAJTWAPT5n4dnsfsM4LlkXN2m3+aeX8iBqK6DgmdFd8orbbxtb+5XwOG0RpYxuNHl98VUDtE2HIjFUoMfUiDcvwcA1IlkLF70rD3G34E1XYh5Qavw7FVO7rbRVRYQST2gtkfPW7YoIwLt9+rF2IyiqxFQEDJgoZ27OXTBa6jGdPUpD+N7+zrejdkJcTsJQY/q47Za8XCHZBVBF0+opMf7l9aRKDZFhnuvADM4LvWar1E6zb/TyQm8x2L/ndQwmcgVSCWeRN4LxsvsHDdfolxGnPlx2I2ukGBfRjhwLRWuHlQ4cnLPk+XAwDJ0SH8BtK0O0Eucg==; bm_sz=E1343231076FA54DCD84408F5860E789~YAAQfLSvw4jDjeN9AQAAu0dzcQ4/ID0DS4EbxhPN+RMwTwVraTia0v1leNe3/Fh4EHxGdYxaAMCNZKBarvFmXAQNd9YpFzns7gCmvBoZsyz2Y3AlKlJ22EpXpYTNaVNHhZyTGs8mOTfymBycEYOv8WkeOP2zHg6gPgF60EHn9xV1bgMtg3bRQQxl/bN3IXUG2+eC9PMCNs+LiUZdnU6a0yKQIECPJ5VC1PgkXq7QfpPXmOXja7GCqdj1kI2TZD0v7fh+j1j+ObAAOhXlgV9qOS0he81MIoYKUtrMxcvBmN/2wcX+oB4=~4276536~3752753; ASP.NET_SessionId=0i5eu3bpq510xxkxmzn0aflt; guestSessionId=4a3d58dc-6e98-414b-b32e-6d4c0f81ee6d; visitorId=67855f45-f71e-48ed-8056-3368f80e4c70', 'sec-ch-ua:  " Not;A Brand";v="99", "Google Chrome";v="97", "Chromium";v="97"', 'sec-ch-ua-mobile:  ?0', 'sec-ch-ua-platform:  "Windows"', 'sec-fetch-dest:  document', 'sec-fetch-mode:  navigate', 'sec-fetch-site:  none', 'sec-fetch-user:  ?1', 'upgrade-insecure-requests:  1', 'user-agent:  Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36'),));

    $response = curl_exec($curl);

    curl_close($curl);
    $arr = json_decode($response);

    return $arr;
}


$arr = getList('https://www.lcwaikiki.com/tr-TR/TR/ajax/Category/CategoryPageData?xhrKeys=CountryCode,CategoryKey,group,gender,xhrKeys&CountryCode=TR&group=2&gender=2');
$categorylist = $arr->CategoryKeyList;

$categories = array();
foreach ($categorylist as $item) {
    $categories[] = $item->CategoryKeyValue;
}
$arr = getList('https://www.lcwaikiki.com/tr-TR/TR/ajax/Category/CategoryPageData?xhrKeys=CountryCode,CategoryKey,group,gender,xhrKeys&CountryCode=TR&group=2&gender=1');
$categorylist = $arr->CategoryKeyList;


foreach ($categorylist as $item) {
    $categories[] = $item->CategoryKeyValue;
}

$categories=array_unique($categories);

//print_r($categories);
$productUrl = array();
foreach ($categories as $c) {
    $page = 1;
    do {
        $arr = getList('https://www.lcwaikiki.com/tr-TR/TR/ajax/Category/CategoryPageData?xhrKeys=CountryCode,CategoryKey,group,gender,xhrKeys&CountryCode=TR&group=2&gender=2&PageIndex=' . $page . '&CategoryKey=' . $c);
        $items=array();
        if ($arr) {
            if ($arr->CatalogList->Items) {
                $items = $arr->CatalogList->Items;

                foreach ($items as $p) {
                    $productUrl[] = $p->ModelUrl;
                }
            }
            $page++;
        }
    } while (count($items) > 95);
}

foreach ($categories as $c) {
    $page = 1;
    do {
        $arr = getList('https://www.lcwaikiki.com/tr-TR/TR/ajax/Category/CategoryPageData?xhrKeys=CountryCode,CategoryKey,group,gender,xhrKeys&CountryCode=TR&group=2&gender=1&PageIndex=' . $page . '&CategoryKey=' . $c);
        $items=array();
        if ($arr) {
            if ($arr->CatalogList->Items) {
                $items = $arr->CatalogList->Items;

                foreach ($items as $p) {
                    $productUrl[] = $p->ModelUrl;
                }
            }
            $page++;
        }
    } while (count($items) > 95);
}
$productUrl=array_unique($productUrl);

$file=fopen('lcwProductLinks.csv','w');
foreach ($productUrl as $url){
    fwrite($file,"https://www.lcwaikiki.com".$url."\n");
}