<?php

function lisaOpilane()
{
    if (!isset($_POST["submit"])) return;

    $required = ['nimi','pilt','isikukood','eriala','linn','maakond','aine1','hinne1','aine2','hinne2'];
    foreach ($required as $r) {
        if (!isset($_POST[$r]) || trim($_POST[$r]) === '') {
            echo "Viga: Väli '$r' on puudu.";
            return;
        }
    }

    $hinne1 = (int)$_POST['hinne1'];
    $hinne2 = (int)$_POST['hinne2'];
    if ($hinne1 < 1 || $hinne1 > 5 || $hinne2 < 1 || $hinne2 > 5) {
        echo "Viga: Hinne peab olema 1–5.";
        return;
    }

    // FIX: versioon peab olema 1.0 mitte 1,0
    $xmlDoc = new DOMDocument("1.0", "UTF-8");
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->formatOutput = true;
    $xmlDoc->load("opilased.xml");

    // ROOT
    $xmlRoot = $xmlDoc->documentElement;

    // UUS OPILANE
    $xmlOpilane = $xmlDoc->createElement("opilane");
    // FIX: ära lisa appendChild dokumendile (see rikkus sul XML-i)
    $xmlRoot->appendChild($xmlOpilane);

    // elukoht
    $elukoht = $xmlDoc->createElement("elukoht");
    $xmlOpilane->appendChild($elukoht);

    // ära salvesta submit nuppu
    unset($_POST["submit"]);

    // lisa väljad, aga ära lisa aine1/hinne1/a ine2/hinne2 otse <opilane> alla
    foreach ($_POST as $voti => $vaartus)
    {
        if ($voti == "aine1" || $voti == "hinne1" || $voti == "aine2" || $voti == "hinne2") {
            continue;
        }

        $kirje = $xmlDoc->createElement($voti, $vaartus);

        if ($voti == "linn" || $voti == "maakond")
            $elukoht->appendChild($kirje);
        else
            $xmlOpilane->appendChild($kirje);
    }

    // 2x aine (nõude järgi)
    $aine1 = $xmlDoc->createElement("aine");
    $aine1->appendChild($xmlDoc->createElement("nimetus", $_POST["aine1"]));
    $aine1->appendChild($xmlDoc->createElement("hinne", $_POST["hinne1"]));
    $xmlOpilane->appendChild($aine1);

    $aine2 = $xmlDoc->createElement("aine");
    $aine2->appendChild($xmlDoc->createElement("nimetus", $_POST["aine2"]));
    $aine2->appendChild($xmlDoc->createElement("hinne", $_POST["hinne2"]));
    $xmlOpilane->appendChild($aine2);

    $xmlDoc->save("opilased.xml");

    // parem kui Refresh:0
    header("Location: ".$_SERVER["PHP_SELF"]);
    exit;
}

$opilased = simplexml_load_file("opilased.xml");

// otsing: eriala/nimi/isikukood + aine nimetus
function erialaOtsing($paring){
    global $opilased;
    $tulemus=array();

    foreach($opilased->opilane as $opilane) {

        if (substr(strtolower((string)$opilane->eriala), 0, strlen($paring)) == strtolower($paring)) {
            array_push($tulemus, $opilane);

        } else if (substr(strtolower((string)$opilane->nimi), 0, strlen($paring)) == strtolower($paring)) {
            array_push($tulemus, $opilane);

        } else if (substr(strtolower((string)$opilane->isikukood), 0, strlen($paring)) == strtolower($paring)) {
            array_push($tulemus, $opilane);

        } else if (isset($opilane->aine)) {
            foreach ($opilane->aine as $aine) {
                if (substr(strtolower((string)$aine->nimetus), 0, strlen($paring)) == strtolower($paring)) {
                    array_push($tulemus, $opilane);
                    break;
                }
            }
        }
    }
    return $tulemus;
}

// käivita lisamine
lisaOpilane();

?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <link href="tabel.css" rel="stylesheet">
    <title>XML faili kuvamine - Opilased.xml</title>
</head>
<body>

<h1>XML faili kuvamine - Opilased.xml</h1>

<form action="?" method="post">
    <label for="otsing"> Otsi:</label>
    <input type="text" name="otsing" id="otsing" placeholder="Nimi | Eriala | Isikukood | Aine">
    <input type="submit" value="OK">
</form>

<?php
// vali kas otsingu tulemus või kõik õpilased
$kuva = $opilased->opilane;
if(!empty($_POST['otsing'])){
    $kuva = erialaOtsing($_POST['otsing']);
}
?>

<table>
    <tr>
        <th>Õpilase nimi</th>
        <th>Pilt</th>
        <th>Isikukood</th>
        <th>Eriala</th>
        <th>Aine 1</th>
        <th>Hinne 1</th>
        <th>Aine 2</th>
        <th>Hinne 2</th>
        <th>Elukoht</th>
    </tr>

    <?php foreach($kuva as $opilane): ?>
        <tr>
            <td><?= htmlspecialchars((string)$opilane->nimi) ?></td>
            <td>
                <?php if (!empty($opilane->pilt)): ?>
                    <img src="<?= htmlspecialchars((string)$opilane->pilt) ?>" style="max-width:120px;max-height:120px;">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string)$opilane->isikukood) ?></td>
            <td><?= htmlspecialchars((string)$opilane->eriala) ?></td>

            <td><?= htmlspecialchars((string)($opilane->aine[0]->nimetus ?? "")) ?></td>
            <td><?= htmlspecialchars((string)($opilane->aine[0]->hinne ?? "")) ?></td>
            <td><?= htmlspecialchars((string)($opilane->aine[1]->nimetus ?? "")) ?></td>
            <td><?= htmlspecialchars((string)($opilane->aine[1]->hinne ?? "")) ?></td>

            <td>
                <?= htmlspecialchars((string)($opilane->elukoht->linn ?? "")) ?>
                <?= !empty($opilane->elukoht->maakond) ? ", ".htmlspecialchars((string)$opilane->elukoht->maakond) : "" ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Lisa õpilane</h2>

<table>
    <form action="" method="post" name="vorm1">
        <tr>
            <td><label for="nimi">Nimi</label></td>
            <td><input type="text" name="nimi" id="nimi"></td>
        </tr>

        <tr>
            <td><label for="pilt">Pilt (URL)</label></td>
            <td><input type="text" name="pilt" id="pilt"></td>
        </tr>

        <tr>
            <td><label for="isikukood">Isikukood:</label></td>
            <td><input type="text" name="isikukood" id="isikukood"></td>
        </tr>

        <tr>
            <td><label for="eriala">Eriala</label></td>
            <td><input type="text" name="eriala" id="eriala"></td>
        </tr>

        <tr>
            <td><label for="aine1">Aine 1</label></td>
            <td><input type="text" name="aine1" id="aine1"></td>
        </tr>
        <tr>
            <td><label for="hinne1">Hinne 1</label></td>
            <td><input type="number" name="hinne1" id="hinne1" min="1" max="5"></td>
        </tr>

        <tr>
            <td><label for="aine2">Aine 2</label></td>
            <td><input type="text" name="aine2" id="aine2"></td>
        </tr>
        <tr>
            <td><label for="hinne2">Hinne 2</label></td>
            <td><input type="number" name="hinne2" id="hinne2" min="1" max="5"></td>
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

</body>
</html>
