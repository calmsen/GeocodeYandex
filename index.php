<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Получение координат дома.</title>
    </head>
    <body>
        <?php
        include "GeocodeYandex.php";
        $gy = new GeocodeYandex("/home/rl/NetBeansProjects/GeocodeYandex/list.xml");
        $gy->setGeocode();
        ?>
    </body>
</html>
