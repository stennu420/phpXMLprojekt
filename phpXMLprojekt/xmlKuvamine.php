<?php

function lisaOpilane()
{
    $required = ['nimi','pilt','isikukood','eriala','linn','maakond','aine1','hinne1','aine2','hinne2'];
    foreach ($required as $r) {
        if (!isset($_POST[$r]) || trim($_POST[$r]) === '') {
            return "Viga: Väli '$r' on puudu.";
        }
    }

    $hinne1 = (int)$_POST['hinne1'];
    $hinne2 = (int)$_POST['hinne2'];
    if ($hinne1 < 1 || $hinne1 > 5 || $hinne2 < 1 || $hinne2 > 5) {
        return "Viga: Hinne peab olema 1–5.";
    }


    $xmlDoc = new DOMDocument("1,0", "UTF-8");
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load("opilased.xml");
    $xmlDoc->formatOutput = true;

    $xmlOpilane = $xmlDoc->createElement("opilane");
    $xmlDoc->appendChild($xmlOpilane);
    $xmlRoot = $xmlDoc->documentElement;
    $xmlRoot->appendChild($xmlOpilane);
    $elukoht = $xmlDoc->createElement("elukoht");
    $xmlOpilane->appendChild($elukoht);
    unset($_POST["submit"]);
    foreach ($_POST as $voti => $vaartus)
    {
        $kirje = $xmlDoc->createElement($voti, $vaartus);

        if ($voti == "linn" || $voti == "maakond")
            $elukoht->appendChild($kirje);
        else
            $xmlOpilane->appendChild($kirje);

    }

    $xmlDoc->save("opilased.xml");
    header("Refresh:0");

}

$opilased = simplexml_load_file("opilased.xml");

//õpilase otsing

?>
<!DOCTYPE html>
<html>
<head>
    <link href="tabel.css" rel="stylesheet">
    <title>XML faili kuvamine - Opilased.xml</title>
</head>
<body>
<h1>XML faili kuvamine - Opilased.xml</h1>
<?php
//1.Õpilase nimi
echo "1.õpilase nimi:".$opilased->opilane[0]->nimi;
//kõik õpilased
function erialaOtsing($paring){
    global $opilased;
    $tulemus=array();
    foreach($opilased->opilane as $opilane) {
        if (substr(strtolower($opilane->eriala), 0, strlen($paring))
            == strtolower($paring)) {
            array_push($tulemus, $opilane);
        } else if (substr(strtolower($opilane->nimi), 0, strlen($paring))
            == strtolower($paring)) {

            array_push($tulemus, $opilane);
        } else if (substr(strtolower($opilane->isikukood), 0, strlen($paring))
            == strtolower($paring)) {

            array_push($tulemus, $opilane);
        }
    }
    return $tulemus;
}

?>
<form action="?" method="post">
    <label for="otsing"> Otsi:</label>
    <input type="text" name="otsing" id="otsing" placeholder="Nimi | Eriala | Isikukood">
    <input type="submit" value="OK">
</form>
<?php
//otsingu tulemus:
if(!empty($_POST['otsing'])){
    $tulemus=erialaOtsing($_POST['otsing']);
    foreach($tulemus as $opilane){
        echo " <table>
    
    <tr>
        <th>Õpilase nimi</th>
        <th>Pilt</th>
        <th>Isikukood</th>
        <th>Eriala</th>
        <th>Aine</th>
        <th>Hinne1</th>
        <th>Hinne2</th>
        <th>Elukoht</th>
    </tr>";


        echo "<tr>";
        echo "<td>".$opilane->nimi."</td>";
        echo "<td>".$opilane->pilt."</td>";
        echo "<td>".$opilane->isikukood."</td>";
        echo "<td>".$opilane->eriala."</td>";
        echo "<td>".$opilane->aine."</td>";
        echo "<td>".$opilane->hinne1."</td>";
        echo "<td>".$opilane->hinne2."</td>";
        echo "<td>".$opilane->elukoht->linn.","
        .$opilane->elukoht->maakond."</td>";
        echo "</tr>";
    }

} else {
?>
<table>
    <tr>
        <th>Õpilase nimi</th>
        <th>Isikukood</th>
        <th>Eriala</th>
        <th>Elukoht</th>
    </tr>
    <?php
    foreach($opilased->opilane as $opilane){
        echo "<tr>";
        echo "<td>".$opilane->nimi."</td>";
        echo "<td>".$opilane->isikukood."</td>";
        echo "<td>".$opilane->eriala."</td>";
        echo "<td>".$opilane->elukoht->linn."
,".$opilane->elukoht->maakond."</td>";
        echo "</tr>";
    }
    }
    ?>
</table>
<table>
    <form action="" method="post" name="vorm1">
        <tr>
            <td><label for="nimi">Nimi</label></td>
            <td><input type="text" name="nimi" id="nimi"></td>
        </tr>
        <tr>
            <td><label for="isikukood">Isikukood:</label></td>
            <td><input type="text" name="isikukood" id="isikukood" ></td>
        </tr>
        <tr>
            <td><label for="eriala">Eriala</label></td>
            <td><input type="text" name="eriala" id="eriala"></td>
        </tr>
        <tr>
            <td><label for="aine">Aine</label></td>
            <td><input type="text" name="aine" id="aine"</td>
        </tr>
        <tr>
            <td><label for="hinne">Hinne</label></td>
            <td><input type="text" name="hinne" id="hinne"></td>
        </tr>
        <tr>
            <td><label for="linn">Linn</label></td>
            <td><input type="text" name="linn" id="linn"></td>
        </tr>
        <tr>
            <td><label for="maakond">Maakond:</label></td>
            <td><input type="text" name="maakond" id="maakond"></td>
        </tr>
        <tr>
            <td><input type="submit" name="submit" id="submit" value="Sisesta"></td>
            <td></td>
        </tr>
    </form>
</table>
<?php
if (isset($_POST["submit"])){
    lisaOpilane();
    echo "Õpilane lisatud";
}
?>
</body>
</html>
