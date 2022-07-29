<html>
<head>
    <title>XML Parser</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body style="margin: 50px">
<h1 style="text-align: left">XML Parser</h1>
    <p>Please enter negative value to add tax</p>
    <form action="parser.php" method="post">
        <select name="supplier" required>
            <option value="">Select Supplier</option>
            <option value="akayev">Akayev</option>
            <option value="alexanderGardi">Alexander Gardi</option>
            <option value="allev">Allev</option>
            <option value="ayakkabiFrekansi">Ayakkabi Frekansi</option>
            <option value="basicToptan">Basic Toptan</option>
            <option value="bayichekich">Bayichekich</option>
            <option value="bigdart">Bigdart</option>
            <option value="buyukbedeniz">Buyukbedeniz</option>
            <option value="cicibebeAyakkabi">Cicibebe Ayakkabi</option>
            <option value="cossta">Cossta</option>
            <option value="degerindenAl">Degerinden Al</option>
            <option value="ebijuteri">E-Bijuteri</option>
            <option value="epocale">Epocale</option>
            <option value="esarpGalasi">Esarp Galasi</option>
            <option value="ews">EWS</option>
            <option value="fularcim">Fularcim</option>
            <option value="gozdeMobilya">Gozde Mobilya</option>
            <option value="guardLeather">Guard Leather</option>
            <option value="gumush">Gumush</option>
            <option value="hasema">Hasema</option>
            <option value="karpefingo">Karpefingo</option>
            <option value="lactone">Lactone</option>
            <option value="lisinya">Lisinya</option>
            <option value="maskButik">Mask Butik</option>
            <option value="midyatGumusDunyasi">Midyat Gumus Dunyasi</option>
            <option value="mobettoMobilya">Mobetto Mobilya</option>
            <option value="modaCizgi">Moda Cizgi</option>
            <option value="modaPinhan">Moda Pinhan</option>
            <option value="modaVitrini">Moda Vitrini</option>
            <option value="moviment">Moviment</option>
            <option value="muzikAletleri">Muzik Aletleri</option>
            <option value="nevresimDunyasi">Nevresim Dunyasi</option>
            <option value="ozpa">Ozpa</option>
            <option value="parla">Parla</option>
            <option value="poniba">Poniba</option>
            <option value="rapellin">Rapellin</option>
            <option value="riccotarz">Riccotarz</option>
            <option value="samursaka">Samursaka</option>
            <option value="sezonTrendi">Sezon Trendi</option>
            <option value="silverSunKids">Silver Sun Kids</option>
            <option value="takistir">Takistir</option>
            <option value="tarzimSuper">Tarzim Super</option>
            <option value="tatlisBebe">Tatlis Bebe</option>
            <option value="tonnyMood">Tonny Mood</option>
            <option value="turemMobilya">Turem Mobilya</option>
            <option value="twenty3">Twenty3</option>
            <option value="vprModa">VPR Moda</option>
            <option value="wagoonAyakkabi">Wagoon Ayakkabi</option>
            <option value="zavansa">Zavansa</option>
        </select>
        <label>Discount % (0-100):</label><input type="number" max="100" min="-100" name="discount" value="0"/>
        <button type="submit">Parse XML</button>
    </form>
</body>
</html>
