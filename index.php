<?php
require ('action/database.php');
include ('layout/header.php');

?>
<?php
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $offset = $page > 1 ? $page * $limit : 0;
    $stmt = $pdo->prepare("SELECT * FROM meteo ORDER BY id DESC LIMIT :limit OFFSET :offset;");
    $stmt->bindParam('limit',$limit, PDO::PARAM_INT);
    $stmt->bindParam('offset',$offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $numberLines = $pdo->query("SELECT COUNT(*) FROM meteo")->fetchColumn();
?>

<?php

$temperature = [];
foreach ($data as $row) {
    $dataTemp = $row;
    $dataTemp['value'] = $row['temperature'];
    $temperature[] = $dataTemp;
}
$temperature = json_encode($temperature);

?>
<?php

$humidite = [];
foreach ($data as $row) {
    $dataHum = $row;
    $dataHum['value'] = $row['humidite'];
    $humidite[] = $dataHum;
}
$humidite = json_encode($humidite);
?>
<body>
<section>
    <h1>Météo</h1>
    <select name="limit" id="limit" class="form-control select">
        <option value="10" <?php if ($limit == '10') { echo 'selected'; }?>>10</option>
        <option value="20" <?php if ($limit == '20') { echo 'selected'; }?>>20</option>
        <option value="30" <?php if ($limit == '30') { echo 'selected'; }?>>30</option>
        <option value="40" <?php if ($limit == '40') { echo 'selected'; }?>>40</option>
        <option value="<?= $numberLines ?>" <?php if ($limit == $numberLines) { echo 'selected'; }?>>Tout</option>
    </select>
    <br><br>
    <button onclick="exportTbToCSVformat('export_details.csv')" class="export">Export to CSV</button>
    <div class="tableauDiv">
        <table class="table">
            <thead>
            <tr>
                <th style="color: white">id</th>
                <th><label style="display: none">Temperature</label><i class="fa-solid fa-temperature-three-quarters" style="color: white"></i></th>
                <th><label style="display: none">Humidite</label><i class="fa-solid fa-droplet" style="color: white"></i></th>
                <th><label style="display: none">date/heure</label><i class="fa-regular fa-clock" style="color: white"></i></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($data as $row) { ?>
            <tr>
                <td class="result"><?php echo $row['id'] ?></td>
                <td class="result"><?php echo $row['temperature'] ?> </td>
                <td class="result"><?php echo $row['humidite'] ?></td>
                <td class="result"><?php $date = date_parse($row['date_heure']);
                echo str_pad($date['day'], 2, 0, STR_PAD_LEFT).'/'.str_pad($date['month'], 2, 0, STR_PAD_LEFT).'/'.$date['year'].' '.str_pad($date['hour'], 2, 0, STR_PAD_LEFT).':'.str_pad($date['minute'], 2, 0, STR_PAD_LEFT); ?></td>
            </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if ($numberLines <= $limit || $page == 1) { echo 'disabled'; } ?>"><a class="page-link" href="?page=1&limit=<?= $limit ?>"><<</a></li>
            <li class="page-item <?php if ($numberLines <= $limit || $page == 1) { echo 'disabled'; } ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>"><</a></li>
            <?php
            if ($numberLines > $limit) {
                for ($i = 0; $i < ceil($numberLines / $limit) - 1; $i++) {
                    if (($i + 1) > $page - 4 && ($i + 1) < $page + 4) {
                        $addClass = '';
                        if ($page == $i + 1) {
                            $addClass = 'active';
                        }
                        echo '<li class="page-item '. $addClass .'"><a class="page-link" href="?page='.($i + 1).'&limit='.$limit.'">'.($i + 1).'</a></li>';
                    }
                }
            }
            ?>
            <li class="page-item <?php if ($numberLines <= $limit || $page == ceil($numberLines / $limit) - 1) { echo 'disabled'; } ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>">></a></li>
            <li class="page-item <?php if ($numberLines <= $limit || $page == ceil($numberLines / $limit) - 1) { echo 'disabled'; } ?>"><a class="page-link" href="?page=<?= ceil($numberLines / $limit) - 1 ?>&limit=<?= $limit ?>">>></a></li>

        </ul>
    </nav>
    <div style="display: flex;justify-content: center; align-items: center; gap: 50px;" id="wrapper">
        <canvas id="canvas" ></canvas>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="./assets/js/canvas2svg.js"></script>
<script src="./assets/js/export.js"></script>
<script>

    let ctxTemp = {
        type: 'line',
        data: {
            datasets: [{
                backgroundColor: 'rgb(222,19,19)',
                data:  <?= $temperature ?>,
                borderColor: 'rgb(222,19,19)',
                label: 'température',
                pointRadius: 5,
            },
            {
                label: 'Humidité',
                data: <?= $humidite ?>,
                borderColor: 'rgb(55,83,246)',
                backgroundColor: 'rgb(55,83,246)',
                pointRadius: 5,
            }]
        },
        options: {
            scales: {
                x: {
                    ticks: {
                        color: '#ffffff'
                    }
                },
                y: {
                    ticks: {
                        color: '#ffffff'
                    }
                }
            },
            parsing: {
                xAxisKey: 'date_heure',
                yAxisKey: 'value'
            },
            plugins: {

                legend: {
                    display: true,
                    labels: {
                        color: 'rgb(255,255,255)',
                    }
                }
            },

            animation:  false,
            responsive: false
        }
    }

    const cfgTemp = document.getElementById('temperatures');
    const myChartTemp = new Chart(ctxTemp, cfgTemp);

    document.getElementById('limit').addEventListener('change', function () {
        window.location.href = 'index.php?limit=' + this.value + '&page=1';
    })

    let width = 600
    let height = 500;

    let context = document.getElementById('canvas');
    context.width = width;
    context.height = height;

    let chart = new Chart(document.getElementById('canvas'), ctxTemp);

    createPngLink('chart.png', 'Export PNG', chart);

    function downloadToCSV(csv, filename) {
        var csvFile;
        var downloadLink;
        // CSV file
        csvFile = new Blob([csv], {type: "text/csv"});
        // Download link
        downloadLink = document.createElement("a");
        // File name
        downloadLink.download = filename;
        // Create a link to the file
        downloadLink.href = window.URL.createObjectURL(csvFile);
        // Hide download link
        downloadLink.style.display = "none";
        // Add the link to DOM
        document.body.appendChild(downloadLink);
        // Click download link
        downloadLink.click();
    }

    function exportTbToCSVformat(filename) {
        var csv = [];
        var rows = document.querySelectorAll("table tr");

        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");

            for (var j = 0; j < cols.length; j++)
                row.push(cols[j].innerText);

            csv.push(row.join(","));
        }
        // Download CSV file
        downloadToCSV(csv.join("\n"), filename);
    }

</script>

</body>


